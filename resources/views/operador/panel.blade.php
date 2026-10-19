@extends('layouts.app')
@section('titulo', 'Mi ventanilla')

@section('contenido')
@if($operador->ventanilla === null)
    <div class="aviso error">Tu usuario no tiene ventanilla asignada. Pide al administrador que te la asigne.</div>
@else
    <h1>{{ $operador->ventanilla->area->nombre }} · {{ $operador->ventanilla->nombre }}</h1>

    @if($actual)
        <div class="tarjeta" style="text-align:center">
            <div class="gris">Atendiendo</div>
            <div class="folio">{{ $actual->folio }}</div>
            <p class="gris">Llamado a las {{ $actual->llamado_en->format('H:i:s') }} · llamadas: {{ $actual->llamadas }}</p>

            <form method="POST" action="{{ route('operador.finalizar', $actual) }}" style="margin:.5rem 0">
                @csrf
                <input type="hidden" name="siguiente" value="1">
                <button class="btn grande" type="submit">Finalizar y llamar siguiente</button>
            </form>

            <div style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap">
                <form method="POST" action="{{ route('operador.finalizar', $actual) }}">
                    @csrf <button class="btn sec" type="submit">Solo finalizar</button>
                </form>
                <form method="POST" action="{{ route('operador.rellamar', $actual) }}">
                    @csrf <button class="btn sec" type="submit">Volver a llamar</button>
                </form>
                <form method="POST" action="{{ route('operador.ausente', $actual) }}"
                      onsubmit="return confirm('¿Marcar como no presentado?')">
                    @csrf <button class="btn peligro" type="submit">No se presentó</button>
                </form>
            </div>
        </div>
    @else
        <div class="tarjeta" style="text-align:center">
            <p class="gris">No tienes un turno en atención.</p>
            <form method="POST" action="{{ route('operador.llamar') }}">
                @csrf <button class="btn grande" type="submit">Llamar siguiente turno</button>
            </form>
        </div>
    @endif

    <div class="rejilla">
        <div class="tarjeta">
            <div class="gris">En espera en tu área</div>
            <div class="folio" id="en-espera">{{ $enEspera }}</div>
            <div class="gris">Próximos: <span id="proximos">—</span></div>
        </div>
        <div class="tarjeta">
            <div class="gris">Tu día de hoy</div>
            <p>Atendidos: <strong>{{ $atendidosHoy }}</strong></p>
            <p>No se presentaron: <strong>{{ $ausentesHoy }}</strong></p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if($operador->ventanilla !== null)
<script>
async function refrescarCola() {
    try {
        const r = await fetch('{{ route('operador.cola') }}', { headers: { 'Accept': 'application/json' } });
        if (!r.ok) return;
        const d = await r.json();
        document.getElementById('en-espera').textContent = d.en_espera;
        document.getElementById('proximos').textContent = d.proximos.length ? d.proximos.join(', ') : '—';
    } catch (e) { /* sin red: se reintenta en el siguiente ciclo */ }
}
refrescarCola();
setInterval(refrescarCola, 5000);
</script>
@endif
@endpush
