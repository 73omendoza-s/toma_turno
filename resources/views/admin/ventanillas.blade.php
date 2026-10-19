@extends('layouts.app')
@section('titulo', 'Ventanillas')

@section('contenido')
<h1>Ventanillas</h1>

<form class="tarjeta" method="POST" action="{{ route('admin.ventanillas.store') }}">
    @csrf
    <strong>Alta de ventanilla</strong>
    <div class="fila-form">
        <div>
            <label for="area_id">Área</label>
            <select id="area_id" name="area_id" required>
                <option value="">— elige —</option>
                @foreach($areas as $a)
                    <option value="{{ $a->id }}" @selected(old('area_id') == $a->id)>{{ $a->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="nombre">Nombre (ej. Ventanilla 1, Caja 3)</label><input id="nombre" name="nombre" value="{{ old('nombre') }}" required></div>
        <div><button class="btn" type="submit">Dar de alta</button></div>
    </div>
</form>

<table>
    <thead><tr><th>Área</th><th>Ventanilla</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($ventanillas as $v)
        <tr>
            <td>{{ $v->area->nombre }}</td>
            <td>{{ $v->nombre }}</td>
            <td><span class="etiqueta {{ $v->activa ? 'activo' : 'inactivo' }}">{{ $v->activa ? 'Activa' : 'Inactiva' }}</span></td>
            <td>
                <form method="POST" action="{{ route('admin.ventanillas.toggle', $v) }}">
                    @csrf @method('PATCH')
                    <button class="btn sec" type="submit">{{ $v->activa ? 'Desactivar' : 'Activar' }}</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
