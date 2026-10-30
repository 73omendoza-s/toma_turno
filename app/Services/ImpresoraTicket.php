<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImpresoraConfig;
use App\Models\Turno;
use Illuminate\Support\Str;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

/** Imprime el ticket directo en la Epson TM-T88V (80 mm) sin pasar por el navegador. */
class ImpresoraTicket
{
    private const ANCHO = 42;       // caracteres por línea (fuente A)
    private const PX = 512;         // ancho de la imagen (seguro para 80 mm; no se desborda)
    private const MARGEN = 16;      // margen lateral dentro de la imagen

    public function imprimir(Turno $turno): void
    {
        $turno->loadMissing('area');

        $adelante = Turno::hoy()->enEspera()->deArea($turno->area_id)
            ->where('id', '<', $turno->id)->count();

        $momento = $turno->created_at ?? now();

        $inst = $this->t(\App\Support\Institucional::get('inst_nombre'));
        $sub = $this->t(\App\Support\Institucional::get('inst_subtitulo'));
        $folio = $this->t($turno->folio);
        $area = $this->t($turno->area->nombre);

        $p = new Printer($this->conector());
        try {
            $p->initialize();
            $p->setFont(Printer::FONT_A);
            $p->setJustification(Printer::JUSTIFY_CENTER);

            $this->logo($p);

            // Encabezado, folio y área en letra gráfica (imagen). Cada bloque: texto, tamaño máx., mín., ancho de corte, espacio posterior.
            $ok = $this->bloques($p, [
                [$inst,       34, 18, 18, 22, true],    // XVIII AYUNTAMIENTO / DE LA PAZ (negrita)
                [$sub,        28, 16, 22, 22, false],   // TESORERIA GENERAL (normal)
                ['SU TURNO',  30, 16, 12, 12, false],   // SU TURNO (normal)
                [$folio,      96, 40, 12, 22, true],    // folio (negrita)
                [$area,       36, 18, 20, 4,  true],
            ]);

            if (! $ok) { // respaldo en texto si no hay GD o fuente
                $p->setEmphasis(true);
                $p->setTextSize(1, 2);
                $p->text(mb_strtoupper($inst) . "\n");
                $p->text(mb_strtoupper($sub) . "\n");
                $p->setTextSize(1, 1);
                $p->feed(1);
                $p->setTextSize(2, 1);
                $p->text("SU TURNO\n");
                $p->setTextSize(4, 2);
                $p->text($folio . "\n");
                $p->setTextSize(2, 1);
                foreach (explode("\n", wordwrap(mb_strtoupper($area), 21, "\n", true)) as $l) {
                    $p->text($l . "\n");
                }
                $p->setTextSize(1, 1);
                $p->setEmphasis(false);
            }
            $p->feed(2);

            // Datos del turno
            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text($this->fila('Fecha:', $momento->format('d/m/Y')));
            $p->text($this->fila('Hora:', $momento->format('H:i')));
            $p->setEmphasis(true);
            $p->text($this->fila('Personas antes que usted:', (string) $adelante));
            $p->setEmphasis(false);
            $p->setJustification(Printer::JUSTIFY_CENTER);

            // Indicación y despedida
            $p->feed(2);
            $p->setEmphasis(true);
            $p->text("Atienda la pantalla de sala\n");
            $p->setEmphasis(false);
            $p->text("Se llamara su folio cuando\nsea su turno.\n");
            $p->feed(1);
            $p->text("Gracias por su paciencia\n");
            $p->text("Conserve este ticket\n");

            $p->feed(4);
            if (ImpresoraConfig::actual()['corte']) {
                $p->cut();
            }
        } finally {
            $p->close();
        }
    }

    /** Ticket de prueba para verificar la configuración desde el módulo de impresora. */
    public function probar(): void
    {
        $c = ImpresoraConfig::actual();
        $modelo = ImpresoraConfig::MODELOS[$c['modelo']]['nombre'] ?? $c['modelo'];

        $p = new Printer($this->conector());
        try {
            $p->initialize();
            $p->setFont(Printer::FONT_A);
            $p->setJustification(Printer::JUSTIFY_CENTER);

            if (! $this->bloques($p, [['PRUEBA', 60, 24, 12, 12, true], [$this->t($modelo), 30, 16, 24, 10, false]])) {
                $p->setTextSize(3, 2);
                $p->text("PRUEBA\n");
                $p->setTextSize(1, 1);
                $p->text($this->t($modelo) . "\n");
            }
            $p->feed(1);
            $p->text('Conexion: ' . $c['conexion'] . "\n");
            $p->text('Destino: ' . $this->t($c['destino']) . "\n");
            $p->text(now()->format('d/m/Y H:i') . "\n");
            $p->feed(1);
            $p->text("Si puede leer este ticket,\nla impresora esta bien configurada.\n");
            $p->feed(4);
            if ($c['corte']) {
                $p->cut();
            }
        } finally {
            $p->close();
        }
    }

    /**
     * Dibuja varios bloques de texto, centrados, con una fuente TTF pesada en una sola imagen.
     * Cada bloque se ajusta solo al ancho disponible. Devuelve false si no es posible (se usa texto).
     *
     * @param array<int, array{0:string,1:int,2:int,3:int,4:int,5:bool}> $bloques (el último valor: true = negrita, false = normal)
     */
    private function bloques(Printer $p, array $bloques): bool
    {
        $fuente = $this->fuente();
        if ($fuente === null || ! extension_loaded('gd') || ! function_exists('imagettftext')) {
            return false;
        }

        $ruta = storage_path('app/ticket_' . uniqid() . '.png');
        try {
            $maxW = self::PX - 2 * self::MARGEN;
            $filas = [];
            $alto = 8;

            foreach ($bloques as [$texto, $max, $min, $corte, $despues, $negrita]) {
                $f = $negrita ? $fuente : ($this->fuenteNormal() ?? $fuente);
                $lineas = explode("\n", wordwrap(mb_strtoupper(trim($texto)), $corte, "\n", true));
                $s = $max;
                foreach ($lineas as $l) {
                    $s = min($s, $this->ajustar($l, $f, $maxW, $max, $min));
                }
                foreach ($lineas as $i => $l) {
                    $b = imagettfbbox($s, 0, $f, $l);
                    $filas[] = [$l, $s, $b, $f];
                    $alto += ($b[1] - $b[7]) + ($i < count($lineas) - 1 ? 8 : $despues);
                }
            }

            $img = imagecreatetruecolor(self::PX, $alto + 4);
            $blanco = imagecolorallocate($img, 255, 255, 255);
            $negro = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, self::PX, $alto + 4, $blanco);

            $y = 8;
            $n = count($filas);
            $idx = 0;
            foreach ($bloques as [$texto, $max, $min, $corte, $despues, $negrita]) {
                $cant = count(explode("\n", wordwrap(mb_strtoupper(trim($texto)), $corte, "\n", true)));
                for ($i = 0; $i < $cant; $i++, $idx++) {
                    [$l, $s, $b, $f] = $filas[$idx];
                    $x = (int) ((self::PX - ($b[2] - $b[0])) / 2 - $b[0]);
                    imagettftext($img, $s, 0, $x, $y - $b[7], $negro, $f, $l);
                    $y += ($b[1] - $b[7]) + ($i < $cant - 1 ? 8 : $despues);
                }
            }

            imagepng($img, $ruta);
            imagedestroy($img);
            $p->bitImage(EscposImage::load($ruta));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        } finally {
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
    }

    private function ajustar(string $texto, string $fuente, int $maxW, int $max, int $min): int
    {
        for ($s = $max; $s > $min; $s--) {
            $b = imagettfbbox($s, 0, $fuente, $texto);
            if (($b[2] - $b[0]) <= $maxW) {
                return $s;
            }
        }

        return $min;
    }

    /** Fuente ancha y pesada. Se puede forzar una propia en storage/app/fonts/ticket.ttf */
    private function fuente(): ?string
    {
        foreach ([
            storage_path('app/fonts/ticket.ttf'),
            'C:/Windows/Fonts/ariblk.ttf',   // Arial Black: ancha y pesada
            'C:/Windows/Fonts/impact.ttf',
            'C:/Windows/Fonts/arialbd.ttf',
        ] as $f) {
            if (is_file($f)) {
                return $f;
            }
        }

        return null;
    }

    /** Fuente normal (sin negrita). Se puede forzar una propia en storage/app/fonts/ticket_normal.ttf */
    private function fuenteNormal(): ?string
    {
        foreach ([
            storage_path('app/fonts/ticket_normal.ttf'),
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/verdana.ttf',
            'C:/Windows/Fonts/calibri.ttf',
        ] as $f) {
            if (is_file($f)) {
                return $f;
            }
        }

        return null;
    }

    private function logo(Printer $p): void
    {
        $ruta = storage_path('app/logo_ticket.png');
        if (! is_file($ruta) || ! (extension_loaded('gd') || extension_loaded('imagick'))) {
            return;
        }
        try {
            $p->bitImage(EscposImage::load($ruta));
            $p->feed(1);
        } catch (\Throwable $e) {
            report($e); // el ticket sale igual, sin logo
        }
    }

    private function conector(): PrintConnector
    {
        $c = ImpresoraConfig::actual();
        $destino = $c['destino'];

        return match ($c['conexion']) {
            'network' => new NetworkPrintConnector($destino, 9100, 5),
            // Pruebas sin impresora: guarda los bytes ESC/POS en storage/app/ticket_prueba.bin
            'archivo' => new FilePrintConnector(storage_path('app/ticket_prueba.bin')),
            default => new WindowsPrintConnector($destino),
        };
    }

    /** Etiqueta a la izquierda y valor a la derecha, en una línea de 42 caracteres. */
    private function fila(string $etiqueta, string $valor): string
    {
        $espacios = max(1, self::ANCHO - strlen($etiqueta) - strlen($valor));

        return $etiqueta . str_repeat(' ', $espacios) . $valor . "\n";
    }

    /** La térmica usa una página de códigos sin acentos por defecto: se transliteran para que no salgan símbolos raros. */
    private function t(?string $texto): string
    {
        return Str::ascii((string) $texto);
    }
}
