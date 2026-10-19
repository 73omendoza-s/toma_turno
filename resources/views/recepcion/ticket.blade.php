@extends('layouts.app')
@section('titulo', 'Turno ' . $turno->folio)

@section('contenido')
<div class="tarjeta" style="max-width:420px;margin:1rem auto;text-align:center">
    <div class="gris">Su turno es</div>
    <div class="folio">{{ $turno->folio }}</div>
    <h2 style="margin:.2rem 0">{{ $turno->area->nombre }}</h2>
    <p>Pase a: <strong>{{ $turno->area->ventanillas->pluck('nombre')->join(' / ') ?: 'ventanilla por asignar' }}</strong></p>
    <p class="gris">Personas antes que usted: {{ $adelante }}<br>{{ $turno->created_at->format('d/m/Y H:i') }}</p>
    <p>Atienda la pantalla de sala: se llamará su folio.</p>
</div>

<p class="no-imprimir" style="text-align:center">
    <button class="btn grande" onclick="window.print()">Imprimir</button>
    <a class="btn grande sec" href="{{ route('recepcion.index') }}">Nuevo turno</a>
</p>
@endsection
