@php use App\Livewire\Estadisticas as E; $r = $this->resumen; $maxH = max(1, max($this->porHora)); @endphp
<div class="est">
    <style>
        .est h1 { color:var(--guinda-osc); font-size:1.6rem; margin:0 0 .2rem; }
        .est h2 { color:var(--guinda-osc); font-size:1.1rem; margin:0 0 .5rem; }
        .est .sub { color:#475569; margin:0 0 .8rem; }
        .est .caja { background:#fff; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.15); padding:1rem; margin-bottom:1rem; overflow-x:auto; }
        .est .filtros { display:flex; flex-wrap:wrap; gap:.8rem; align-items:flex-end; border-top:4px solid var(--guinda); }
        .est .filtros label { font-size:.85rem; color:#475569; }
        .est .filtros input, .est .filtros select { display:block; margin:.2rem 0 0; padding:.6rem .7rem; font-size:1rem; }
        .est .filtros button { background:var(--guinda) !important; color:#fff !important; border:0; border-radius:8px; padding:.7rem 1.2rem; font-weight:600; cursor:pointer; }
        .est .filtros button:hover { background:var(--guinda-osc) !important; }
        .est .dir-fija { background:color-mix(in srgb, var(--guinda) 10%, white); color:var(--guinda-osc); border-radius:8px; padding:.55rem .9rem; font-weight:700; }
        .est .sin-dir { background:#fff7e0; border-left:5px solid #b45309; padding:8px 12px; border-radius:6px; margin-bottom:1rem; }
        .est .tarjetas { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.8rem; margin-bottom:1rem; text-align:center; }
        .est .tarjetas div { background:#fff; border-radius:8px; border-top:4px solid var(--guinda); box-shadow:0 1px 4px rgba(0,0,0,.15); padding:.8rem; }
        .est .tarjetas b { display:block; font-size:1.6rem; }
        .est .tarjetas span { font-size:.8rem; color:#64748b; }
        .est table { margin-top:0; }
        .est .barras { display:flex; align-items:flex-end; gap:.5rem; height:160px; }
        .est .barras > div { flex:1; text-align:center; font-size:.8rem; }
        .est .barras i { display:block; background:var(--guinda); border-radius:4px 4px 0 0; }
    </style>

    <h1>Estadísticas de atención</h1>
    <p class="sub">Dirección: <b>{{ $this->nombreDireccion }}</b></p>

    @if($this->esSupervisor && ! auth()->user()->direccion_id)
        <div class="sin-dir">Tu usuario no tiene una dirección asignada, por eso no se muestran datos. Pide al administrador que te la asigne en Usuarios.</div>
    @endif

    <div class="caja filtros">
        <label>Desde<input type="date" wire:model.live="desde"></label>
        <label>Hasta<input type="date" wire:model.live="hasta"></label>

        @if($this->esSupervisor)
            <div class="dir-fija">{{ $this->nombreDireccion }}</div>
        @else
            <label>Dirección
                <select wire:model.live="direccionId">
                    <option value="">Todas</option>
                    @foreach ($this->direcciones as $d)<option value="{{ $d->id }}">{{ $d->nombre }}</option>@endforeach
                </select></label>
        @endif

        <label>Área
            <select wire:model.live="areaId">
                <option value="">Todas</option>
                @foreach ($this->areas as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
            </select></label>
        <label>Ventanilla
            <select wire:model.live="ventanillaId">
                <option value="">Todas</option>
                @foreach ($this->ventanillas as $v)<option value="{{ $v->id }}">{{ $v->nombre }}</option>@endforeach
            </select></label>
        <button type="button" wire:click="exportar" wire:loading.attr="disabled" wire:target="exportar">
            <span wire:loading.remove wire:target="exportar">Exportar a Excel</span>
            <span wire:loading wire:target="exportar">Generando...</span>
        </button>
    </div>

    <div class="tarjetas">
        @foreach ([['Emitidos',$r->emitidos],['Atendidos',$r->atendidos],['Ausentes',$r->ausentes],['Abiertos',$r->abiertos],['Espera prom.',E::mmss($r->seg_espera)],['Atención prom.',E::mmss($r->seg_atencion)]] as [$t,$v])
            <div><b>{{ $v }}</b><span>{{ $t }}</span></div>
        @endforeach
    </div>

    @php $suma = (int) $r->atendidos + (int) $r->ausentes + (int) $r->abiertos; @endphp
    <p class="sub" style="text-align:center">
        Emitidos <b>{{ (int) $r->emitidos }}</b> = Atendidos <b>{{ (int) $r->atendidos }}</b> + Ausentes <b>{{ (int) $r->ausentes }}</b> + Abiertos <b>{{ (int) $r->abiertos }}</b>
        @if($suma === (int) $r->emitidos) <span style="color:#15803d;font-weight:700">✓ la suma cuadra</span>
        @else <span style="color:#b91c1c;font-weight:700">⚠ no cuadra (hay turnos en otro estado)</span> @endif
    </p>

    @if(! $this->esSupervisor && ! $this->direccionId)
        <div class="caja">
            <h2>Por dirección</h2>
            <table>
                <tr><th>Dirección</th><th>Emitidos</th><th>Atendidos</th><th>Ausentes</th><th>Abiertos</th><th>Espera prom.</th><th>Atención prom.</th></tr>
                @forelse ($this->porDireccion as $d)
                    <tr><td><b>{{ $d->direccion }}</b></td><td>{{ $d->emitidos }}</td><td>{{ $d->atendidos }}</td><td>{{ $d->ausentes }}</td><td>{{ $d->abiertos }}</td><td>{{ E::mmss($d->seg_espera) }}</td><td>{{ E::mmss($d->seg_atencion) }}</td></tr>
                @empty
                    <tr><td colspan="7">Sin datos en el periodo.</td></tr>
                @endforelse
            </table>
        </div>
    @endif

    <div class="caja">
        <h2>Rendimiento por ventanilla y operador</h2>
        <table>
            <tr><th>Dirección</th><th>Área</th><th>Ventanilla</th><th>Operador</th><th>Total</th><th>Atendidos</th><th>Ausentes</th><th>Abiertos</th><th>Atención prom.</th><th>Espera prom.</th></tr>
            @forelse ($this->porOperador as $o)
                <tr><td>{{ $o->direccion }}</td><td>{{ $o->area }}</td><td><b>{{ $o->ventanilla }}</b></td><td>{{ $o->operador }}</td><td><b>{{ $o->total }}</b></td><td>{{ $o->atendidos }}</td><td>{{ $o->ausentes }}</td><td>{{ $o->abiertos }}</td><td>{{ E::mmss($o->seg_atencion) }}</td><td>{{ E::mmss($o->seg_espera) }}</td></tr>
            @empty
                <tr><td colspan="10">Sin datos en el periodo.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="caja">
        <h2>Por área</h2>
        <table>
            <tr><th>Dirección</th><th>Área</th><th>Emitidos</th><th>Atendidos</th><th>Ausentes</th><th>Abiertos</th><th>Espera prom.</th><th>Atención prom.</th></tr>
            @forelse ($this->porArea as $a)
                <tr><td>{{ $a->direccion }}</td><td>{{ $a->area }}</td><td>{{ $a->emitidos }}</td><td>{{ $a->atendidos }}</td><td>{{ $a->ausentes }}</td><td>{{ $a->abiertos }}</td><td>{{ E::mmss($a->seg_espera) }}</td><td>{{ E::mmss($a->seg_atencion) }}</td></tr>
            @empty
                <tr><td colspan="8">Sin datos en el periodo.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="caja">
        <h2>Turnos por hora (horas pico)</h2>
        <div class="barras">
            @foreach ($this->porHora as $h => $n)
                <div>
                    <i style="height:{{ round($n / $maxH * 120) }}px"></i>
                    <div>{{ $n }}</div><div style="color:#64748b">{{ $h }}h</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

