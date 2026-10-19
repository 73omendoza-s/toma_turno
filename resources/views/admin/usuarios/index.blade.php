@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('contenido')
<h1>Usuarios</h1>

<form class="tarjeta" method="POST" action="{{ route('admin.usuarios.store') }}">
    @csrf
    <strong>Alta de usuario</strong>
    <div class="fila-form">
        <div><label for="name">Nombre completo</label><input id="name" name="name" value="{{ old('name') }}" required></div>
        <div><label for="usuario">Usuario</label><input id="usuario" name="usuario" value="{{ old('usuario') }}" autocomplete="off" required></div>
        <div><label for="password">Contraseña (mín. 8)</label><input id="password" name="password" type="password" autocomplete="new-password" required></div>
        <div>
            <label for="rol">Rol</label>
            <select id="rol" name="rol" required>
                @foreach($roles as $r)
                    <option value="{{ $r->value }}" @selected(old('rol', 'ventanilla') === $r->value)>{{ $r->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="ventanilla_id">Ventanilla (solo operadores)</label>
            <select id="ventanilla_id" name="ventanilla_id">
                <option value="">—</option>
                @foreach($ventanillas as $v)
                    <option value="{{ $v->id }}" @selected(old('ventanilla_id') == $v->id)>{{ $v->area->nombre }} — {{ $v->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div><label><input type="checkbox" name="activo" value="1" checked> Activo</label></div>
        <div><button class="btn" type="submit">Dar de alta</button></div>
    </div>
</form>

<table>
    <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Ventanilla</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($usuarios as $u)
        <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->usuario }}</td>
            <td>{{ $u->rol->etiqueta() }}</td>
            <td>{{ $u->ventanilla ? $u->ventanilla->area->nombre . ' — ' . $u->ventanilla->nombre : '—' }}</td>
            <td><span class="etiqueta {{ $u->activo ? 'activo' : 'inactivo' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span></td>
            <td><a class="btn sec" href="{{ route('admin.usuarios.edit', $u) }}">Editar</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
