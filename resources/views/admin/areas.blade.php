@extends('layouts.app')
@section('titulo', 'Áreas')

@section('contenido')
<h1>Áreas de atención</h1>

<form class="tarjeta" method="POST" action="{{ route('admin.areas.store') }}">
    @csrf
    <strong>Alta de área</strong>
    <div class="fila-form">
        <div><label for="nombre">Nombre</label><input id="nombre" name="nombre" value="{{ old('nombre') }}" required></div>
        <div><label for="prefijo">Prefijo del folio (ej. CAJ)</label><input id="prefijo" name="prefijo" value="{{ old('prefijo') }}" maxlength="5" required></div>
        <div><label for="orden">Orden (opcional)</label><input id="orden" name="orden" type="number" min="0" value="{{ old('orden') }}"></div>
        <div><button class="btn" type="submit">Dar de alta</button></div>
    </div>
</form>

<table>
    <thead><tr><th>Orden</th><th>Área</th><th>Prefijo</th><th class="num">Ventanillas</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($areas as $a)
        <tr>
            <td>{{ $a->orden }}</td>
            <td>{{ $a->nombre }}</td>
            <td>{{ $a->prefijo }}</td>
            <td class="num">{{ $a->ventanillas_count }}</td>
            <td><span class="etiqueta {{ $a->activa ? 'activo' : 'inactivo' }}">{{ $a->activa ? 'Activa' : 'Inactiva' }}</span></td>
            <td>
                <form method="POST" action="{{ route('admin.areas.toggle', $a) }}">
                    @csrf @method('PATCH')
                    <button class="btn sec" type="submit">{{ $a->activa ? 'Desactivar' : 'Activar' }}</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
