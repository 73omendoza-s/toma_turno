<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ImpresoraConfig;
use App\Services\ImpresoraTicket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Impresora térmica')]
class ConfigImpresora extends Component
{
    public string $modelo = 'tm-t88v';
    public string $conexion = 'windows';
    public string $destino = '';
    public bool $directa = false;
    public bool $corte = true;

    /** @var array<int,string> */
    public array $detectadas = [];
    public string $mensaje = '';
    public string $tipoMensaje = 'ok';

    public function mount(): void
    {
        foreach (ImpresoraConfig::actual() as $k => $v) {
            $this->$k = $v;
        }
        if ($this->conexion === 'windows') {
            $this->detectadas = $this->listarCompartidas(); // llena la lista de impresoras al abrir la pantalla
        }
    }

    /** @return array<int,string> */
    private function listarCompartidas(): array
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! function_exists('exec')) {
            return [];
        }
        $salida = [];
        @exec('powershell -NoProfile -Command "Get-Printer | Where-Object Shared | Select-Object -ExpandProperty ShareName"', $salida);

        return array_values(array_filter(array_map('trim', $salida)));
    }

    /** Al cambiar de modelo, sugiere el nombre compartido si el destino está vacío o es el del otro modelo. */
    public function updatedModelo(string $valor): void
    {
        $nombres = array_column(ImpresoraConfig::MODELOS, 'compartida');
        if ($this->conexion === 'windows' && ($this->destino === '' || in_array($this->destino, $nombres, true))) {
            $this->destino = ImpresoraConfig::MODELOS[$valor]['compartida'] ?? $this->destino;
        }
    }

    /** Lista las impresoras compartidas de esta PC (solo funciona si el servidor es Windows). */
    public function detectar(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->aviso('La detección automática solo funciona en un servidor Windows.', 'error');

            return;
        }
        $this->detectadas = $this->listarCompartidas();
        $this->aviso(
            $this->detectadas ? count($this->detectadas) . ' impresora(s) compartida(s) encontrada(s).' : 'No se encontraron impresoras compartidas.',
            $this->detectadas ? 'ok' : 'error'
        );
    }

    public function elegir(string $nombre): void
    {
        $this->destino = $nombre;
    }

    public function guardar(): void
    {
        $this->persistir();
        $this->aviso('Configuración guardada.', 'ok');
    }

    public function probarConexion(): void
    {
        $this->persistir();

        if ($this->conexion === 'network') {
            $e = 0;
            $s = '';
            $f = @fsockopen($this->destino, 9100, $e, $s, 3);
            if ($f) {
                fclose($f);
                $this->aviso("La impresora responde en {$this->destino}:9100.", 'ok');
            } else {
                $this->aviso("No hay respuesta de {$this->destino}:9100. Revise IP, cable y que la impresora esté encendida.", 'error');
            }

            return;
        }

        if ($this->conexion === 'archivo') {
            $this->aviso('Modo prueba: no hay impresora que comprobar, los tickets se guardan en un archivo.', 'ok');

            return;
        }

        $this->detectar();
        $existe = collect($this->detectadas)->contains(fn ($n) => strcasecmp($n, ltrim(strrchr('/' . str_replace('\\', '/', $this->destino), '/'), '/')) === 0);
        $this->aviso(
            $existe ? 'La impresora compartida existe en esta PC.' : 'No se encontró esa impresora compartida. Revise el nombre.',
            $existe ? 'ok' : 'error'
        );
    }

    public function imprimirPrueba(): void
    {
        $this->persistir();
        try {
            app(ImpresoraTicket::class)->probar();
            $this->aviso('Prueba enviada. Revise que haya salido el ticket.', 'ok');
        } catch (\Throwable $e) {
            report($e);
            $this->aviso('No se pudo imprimir: ' . $e->getMessage(), 'error');
        }
    }

    private function persistir(): void
    {
        $this->validate([
            'modelo' => 'required|in:' . implode(',', array_keys(ImpresoraConfig::MODELOS)),
            'conexion' => 'required|in:windows,network,archivo',
            'destino' => 'required_unless:conexion,archivo|nullable|string|max:150',
            'directa' => 'boolean',
            'corte' => 'boolean',
        ]);

        ImpresoraConfig::updateOrCreate(['id' => 1], [
            'modelo' => $this->modelo,
            'conexion' => $this->conexion,
            'destino' => $this->destino,
            'directa' => $this->directa,
            'corte' => $this->corte,
        ]);
    }

    private function aviso(string $texto, string $tipo): void
    {
        $this->mensaje = $texto;
        $this->tipoMensaje = $tipo;
    }

    public function render()
    {
        return view('livewire.config-impresora', ['modelos' => ImpresoraConfig::MODELOS]);
    }
}
