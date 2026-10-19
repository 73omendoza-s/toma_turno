@extends('layouts.app')
@section('titulo', 'Recepción')

@section('contenido')
<h1>Recepción · Emitir turno</h1>
<p class="gris">Elige el área a la que va el contribuyente. Se imprime el folio y se le indica la ventanilla.</p>

<div class="rejilla">
@foreach($areas as $area)
    <form class="tarjeta" method="POST" action="{{ route('recepcion.turnos.store', $area) }}"
          onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <strong>{{ $area->nombre }}</strong>
        <p class="gris" style="margin:.4rem 0">
            {{ $area->ventanillas->pluck('nombre')->join(' · ') ?: 'Sin ventanillas activas' }}
        </p>
        <p style="margin:.4rem 0">En espera: <strong>{{ $area->en_espera }}</strong></p>
        <button class="btn" type="submit" style="width:100%" @disabled($area->ventanillas->isEmpty())>
            Sacar turno {{ $area->prefijo }}
        </button>
    </form>
@endforeach
</div>

<h2>Últimos turnos emitidos hoy</h2>
<table>
    <thead><tr><th>Folio</th><th>Área</th><th>Hora</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse($recientes as $t)
        <tr>
            <td><a href="{{ route('recepcion.turnos.show', $t) }}">{{ $t->folio }}</a></td>
            <td>{{ $t->area->nombre }}</td>
            <td>{{ $t->created_at->format('H:i') }}</td>
            <td>{{ $t->estado->etiqueta() }}</td>
        </tr>
    @empty
        <tr><td colspan="4" class="gris">Aún no se han emitido turnos hoy.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
