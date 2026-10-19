<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Support\Institucional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Estadísticas de atención. El administrador ve todo (y puede filtrar por dirección);
 * un supervisor solo ve las áreas de la dirección que tiene asignada.
 */
class Estadisticas extends Component
{
    // ---- AJUSTA AQUÍ si tus nombres reales difieren (ver migración) ----
    private const COL_NOMBRE_USUARIO = 'users.name'; // o 'users.usuario'

    private const EST_ESPERA = 'espera';

    private const EST_ATENDIENDO = 'atendiendo';

    private const EST_FINALIZADO = 'finalizado';

    private const EST_AUSENTE = 'ausente';
    // --------------------------------------------------------------------

    public string $desde = '';

    public string $hasta = '';

    public ?int $direccionId = null;

    public ?int $areaId = null;

    public ?int $ventanillaId = null;

    public function mount(): void
    {
        abort_unless(in_array(auth()->user()->rol->value, ['admin', 'supervisor'], true), 403);
        $this->desde = $this->hasta = today()->toDateString();
    }

    public function updatedDireccionId(): void
    {
        $this->areaId = $this->ventanillaId = null;
    }

    public function updatedAreaId(): void
    {
        $this->ventanillaId = null;
    }

    public function updatedDesde(): void
    {
        if ($this->hasta < $this->desde) {
            $this->hasta = $this->desde;
        }
    }

    public function updatedHasta(): void
    {
        if ($this->hasta < $this->desde) {
            $this->desde = $this->hasta;
        }
    }

    #[Computed]
    public function esSupervisor(): bool
    {
        return auth()->user()->rol->value === 'supervisor';
    }

    /**
     * Dirección que realmente se aplica. El supervisor solo ve la suya: no se confía en lo que envíe el navegador.
     * Un supervisor sin dirección asignada no ve nada (id 0).
     */
    private function direccionEfectiva(): ?int
    {
        if ($this->esSupervisor) {
            return (int) (auth()->user()->direccion_id ?? 0);
        }

        return $this->direccionId ?: null;
    }

    #[Computed]
    public function nombreDireccion(): string
    {
        $dir = $this->direccionEfectiva();
        if ($dir === null) {
            return 'Todas';
        }

        return (string) (DB::table('direcciones')->where('id', $dir)->value('nombre') ?? 'Sin dirección asignada');
    }

    /** Base filtrada por la columna fecha (no por timestamps) y por dirección. */
    private function base()
    {
        $dir = $this->direccionEfectiva();

        return DB::table('turnos')
            ->join('areas', 'areas.id', '=', 'turnos.area_id')
            ->leftJoin('direcciones', 'direcciones.id', '=', 'areas.direccion_id')
            ->whereBetween('turnos.fecha', [$this->desde, $this->hasta])
            ->when($dir !== null, fn ($q) => $q->where('areas.direccion_id', $dir))
            ->when($this->areaId, fn ($q) => $q->where('turnos.area_id', $this->areaId))
            ->when($this->ventanillaId, fn ($q) => $q->where('turnos.ventanilla_id', $this->ventanillaId));
    }

    #[Computed]
    public function direcciones()
    {
        return DB::table('direcciones')->orderBy('orden')->orderBy('nombre')->get(['id', 'nombre']);
    }

    #[Computed]
    public function areas()
    {
        $dir = $this->direccionEfectiva();

        return DB::table('areas')
            ->when($dir !== null, fn ($q) => $q->where('direccion_id', $dir))
            ->orderBy('nombre')->get(['id', 'nombre']);
    }

    #[Computed]
    public function ventanillas()
    {
        $dir = $this->direccionEfectiva();

        return DB::table('ventanillas')
            ->join('areas', 'areas.id', '=', 'ventanillas.area_id')
            ->when($dir !== null, fn ($q) => $q->where('areas.direccion_id', $dir))
            ->when($this->areaId, fn ($q) => $q->where('ventanillas.area_id', $this->areaId))
            ->orderBy('ventanillas.nombre')->get(['ventanillas.id', 'ventanillas.nombre']);
    }

    #[Computed]
    public function resumen()
    {
        return $this->base()->selectRaw(
            '
            COUNT(*) AS emitidos,
            COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS atendidos,
            COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS ausentes,
            COUNT(CASE WHEN turnos.estado IN (?, ?) THEN 1 END) AS abiertos,
            AVG(turnos.espera_seg) AS seg_espera,
            AVG(CASE WHEN turnos.estado = ? THEN turnos.atencion_seg END) AS seg_atencion',
            [self::EST_FINALIZADO, self::EST_AUSENTE, self::EST_ESPERA, self::EST_ATENDIENDO, self::EST_FINALIZADO]
        )->first();
    }

    /** Totales por dirección (solo lo ve el administrador cuando no filtra una dirección). */
    #[Computed]
    public function porDireccion()
    {
        return $this->base()
            ->selectRaw(
                "COALESCE(direcciones.nombre, 'Sin dirección') AS direccion,
                COUNT(*) AS emitidos,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS atendidos,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS ausentes,
                COUNT(CASE WHEN turnos.estado IN (?, ?) THEN 1 END) AS abiertos,
                AVG(turnos.espera_seg) AS seg_espera,
                AVG(CASE WHEN turnos.estado = ? THEN turnos.atencion_seg END) AS seg_atencion",
                [self::EST_FINALIZADO, self::EST_AUSENTE, self::EST_ESPERA, self::EST_ATENDIENDO, self::EST_FINALIZADO]
            )
            ->groupBy('direcciones.id', 'direcciones.nombre')
            ->orderBy('direcciones.nombre')
            ->get();
    }

    /**
     * Incluye una fila "Sin asignar" con los turnos que siguen en espera y todavía no los llamó nadie,
     * para que la suma por operador cuadre con el total emitido.
     */
    #[Computed]
    public function porOperador()
    {
        return $this->base()
            ->leftJoin('users', 'users.id', '=', 'turnos.user_id')
            ->leftJoin('ventanillas', 'ventanillas.id', '=', 'turnos.ventanilla_id')
            ->selectRaw(
                "COALESCE(direcciones.nombre, 'Sin dirección') AS direccion, areas.nombre AS area,
                COALESCE(ventanillas.nombre, '—') AS ventanilla, COALESCE(".self::COL_NOMBRE_USUARIO.", 'Sin asignar') AS operador,
                COUNT(*) AS total,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS atendidos,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS ausentes,
                COUNT(CASE WHEN turnos.estado IN (?, ?) THEN 1 END) AS abiertos,
                AVG(CASE WHEN turnos.estado = ? THEN turnos.atencion_seg END) AS seg_atencion,
                AVG(turnos.espera_seg) AS seg_espera",
                [self::EST_FINALIZADO, self::EST_AUSENTE, self::EST_ESPERA, self::EST_ATENDIENDO, self::EST_FINALIZADO]
            )
            ->groupBy('direcciones.nombre', 'areas.nombre', 'ventanillas.nombre', 'turnos.user_id', self::COL_NOMBRE_USUARIO)
            ->orderBy('direcciones.nombre')->orderBy('areas.nombre')->orderBy('ventanillas.nombre')
            ->get();
    }

    #[Computed]
    public function porArea()
    {
        return $this->base()
            ->selectRaw(
                "COALESCE(direcciones.nombre, 'Sin dirección') AS direccion, areas.nombre AS area,
                COUNT(*) AS emitidos,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS atendidos,
                COUNT(CASE WHEN turnos.estado = ? THEN 1 END) AS ausentes,
                COUNT(CASE WHEN turnos.estado IN (?, ?) THEN 1 END) AS abiertos,
                AVG(turnos.espera_seg) AS seg_espera,
                AVG(CASE WHEN turnos.estado = ? THEN turnos.atencion_seg END) AS seg_atencion",
                [self::EST_FINALIZADO, self::EST_AUSENTE, self::EST_ESPERA, self::EST_ATENDIENDO, self::EST_FINALIZADO]
            )
            ->groupBy('areas.id', 'areas.nombre', 'direcciones.nombre')
            ->orderBy('direcciones.nombre')->orderBy('areas.nombre')
            ->get();
    }

    /** Turnos emitidos por hora local (se agrupa en PHP: evita diferencias entre MySQL y PostgreSQL). */
    #[Computed]
    public function porHora(): array
    {
        $horas = array_fill_keys(range(7, 18), 0);
        foreach ($this->base()->pluck('turnos.created_at') as $ts) {
            $h = Carbon::parse($ts)->hour; // la base guarda la hora local (zona de la app), sin conversión
            $horas[$h] = ($horas[$h] ?? 0) + 1;
        }
        ksort($horas);

        return $horas;
    }

    /** Exporta a Excel (.xlsx) con los mismos filtros de la pantalla. */
    public function exportar()
    {
        abort_unless(in_array(auth()->user()->rol->value, ['admin', 'supervisor'], true), 403);

        $r = $this->resumen;
        $area = $this->areaId ? ($this->areas->firstWhere('id', $this->areaId)->nombre ?? '') : 'Todas';
        $vent = $this->ventanillaId ? ($this->ventanillas->firstWhere('id', $this->ventanillaId)->nombre ?? '') : 'Todas';

        $libro = new Spreadsheet;

        $this->hoja($libro->getActiveSheet(), 'Resumen', ['Concepto', 'Valor'], [
            ['Desde', $this->desde],
            ['Hasta', $this->hasta],
            ['Dirección', $this->nombreDireccion],
            ['Área', $area],
            ['Ventanilla', $vent],
            ['Emitidos', (int) $r->emitidos],
            ['Atendidos', (int) $r->atendidos],
            ['Ausentes', (int) $r->ausentes],
            ['Abiertos', (int) $r->abiertos],
            ['Espera promedio (min:seg)', self::mmss($r->seg_espera)],
            ['Atención promedio (min:seg)', self::mmss($r->seg_atencion)],
        ]);

        if (! $this->esSupervisor && ! $this->direccionId) {
            $this->hoja(
                $libro->createSheet(),
                'Por dirección',
                ['Dirección', 'Emitidos', 'Atendidos', 'Ausentes', 'Abiertos', 'Espera prom.', 'Atención prom.'],
                $this->porDireccion->map(fn ($d) => [
                    $d->direccion,
                    (int) $d->emitidos,
                    (int) $d->atendidos,
                    (int) $d->ausentes,
                    (int) $d->abiertos,
                    self::mmss($d->seg_espera),
                    self::mmss($d->seg_atencion),
                ])->all()
            );
        }

        $this->hoja(
            $libro->createSheet(),
            'Por operador',
            ['Dirección', 'Área', 'Ventanilla', 'Operador', 'Total', 'Atendidos', 'Ausentes', 'Abiertos', 'Atención prom.', 'Espera prom.'],
            $this->porOperador->map(fn ($o) => [
                $o->direccion,
                $o->area,
                $o->ventanilla,
                $o->operador,
                (int) $o->total,
                (int) $o->atendidos,
                (int) $o->ausentes,
                (int) $o->abiertos,
                self::mmss($o->seg_atencion),
                self::mmss($o->seg_espera),
            ])->all()
        );

        $this->hoja(
            $libro->createSheet(),
            'Por área',
            ['Dirección', 'Área', 'Emitidos', 'Atendidos', 'Ausentes', 'Abiertos', 'Espera prom.', 'Atención prom.'],
            $this->porArea->map(fn ($a) => [
                $a->direccion,
                $a->area,
                (int) $a->emitidos,
                (int) $a->atendidos,
                (int) $a->ausentes,
                (int) $a->abiertos,
                self::mmss($a->seg_espera),
                self::mmss($a->seg_atencion),
            ])->all()
        );

        $horas = [];
        foreach ($this->porHora as $h => $n) {
            $horas[] = [$h.':00', $n];
        }
        $this->hoja($libro->createSheet(), 'Por hora', ['Hora', 'Turnos emitidos'], $horas);

        $libro->setActiveSheetIndex(0);
        $nombre = "estadisticas_{$this->desde}_{$this->hasta}.xlsx";

        return response()->streamDownload(function () use ($libro) {
            (new Xlsx($libro))->save('php://output');
        }, $nombre, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function hoja($hoja, string $titulo, array $encabezados, array $filas): void
    {
        $hoja->setTitle($titulo);
        $hoja->fromArray($encabezados, null, 'A1');
        if ($filas) {
            $hoja->fromArray($filas, null, 'A2', true); // true: los ceros se escriben como 0, no como celda vacía
        }
        $ultima = $hoja->getHighestColumn();
        $hoja->getStyle("A1:{$ultima}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ltrim(Institucional::color('color_primario'), '#')]],
        ]);
        foreach (range('A', $ultima) as $col) {
            $hoja->getColumnDimension($col)->setAutoSize(true);
        }
    }

    public static function mmss($seg): string
    {
        if ($seg === null) {
            return '--';
        }
        $s = (int) round((float) $seg);

        return intdiv($s, 60).':'.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.estadisticas')->layout('components.layouts.app');
    }
}
