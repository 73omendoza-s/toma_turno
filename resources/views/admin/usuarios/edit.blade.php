@extends('layouts.app')
@section('titulo', 'Editar usuario')

@section('contenido')
<h1>Editar usuario · {{ $usuario->usuario }}</h1>

<form class="tarjeta" method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" style="max-width:520px">
    @csrf @method('PUT')
    <label for="name">Nombre completo</label>
    <input id="name" name="name" value="{{ old('name', $usuario->name) }}" required>

    <label for="usuario">Usuario</label>
    <input id="usuario" name="usuario" value="{{ old('usuario', $usuario->usuario) }}" required>

    <label for="password">Nueva contraseña (déjala vacía para no cambiarla)</label>
    <input id="password" name="password" type="password" autocomplete="new-password">

    <label for="rol">Rol</label>
    <select id="rol" name="rol" required>
        @foreach($roles as $r)
            <option value="{{ $r->value }}" @selected(old('rol', $usuario->rol->value) === $r->value)>{{ $r->etiqueta() }}</option>
        @endforeach
    </select>

    <label for="ventanilla_id">Ventanilla (solo operadores)</label>
    <select id="ventanilla_id" name="ventanilla_id">
        <option value="">—</option>
        @foreach($ventanillas as $v)
            <option value="{{ $v->id }}" @selected(old('ventanilla_id', $usuario->ventanilla_id) == $v->id)>{{ $v->area->nombre }} — {{ $v->nombre }}</option>
        @endforeach
    </select>

    <label><input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo))> Activo</label>

    <p>
        <button class="btn" type="submit">Guardar</button>
        <a class="btn sec" href="{{ route('admin.usuarios.index') }}">Cancelar</a>
    </p>
</form>
@endsection
