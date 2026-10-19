<div class="usuarios">
  <style>
    .usuarios h2 { color:var(--guinda-osc); font-size:1.6rem; font-weight:700; margin-bottom:12px; }
    .usuarios form { background:#fff; border-top:4px solid var(--guinda); border-radius:8px; padding:20px;
      box-shadow:0 1px 4px rgba(0,0,0,.15); margin-bottom:24px; display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; }
    .usuarios form > strong { flex-basis:100%; color:var(--guinda-osc); font-size:1.15rem; }
    .usuarios input, .usuarios select { font-size:1.1rem; padding:14px 16px; min-width:260px; flex:1 1 260px;
      border:2px solid #cfcfcf; border-radius:8px; background:#fff; outline:none; }
    .usuarios input:focus, .usuarios select:focus { border-color:var(--guinda); box-shadow:0 0 0 3px rgba(var(--guinda-rgb), .2); }
    .usuarios button { font-size:1rem; font-weight:600; padding:12px 22px; border:0; border-radius:8px;
      background:var(--guinda) !important; color:#fff !important; cursor:pointer; }
    .usuarios button:hover { background:var(--guinda-osc) !important; }
    .usuarios table { border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden;
      box-shadow:0 1px 4px rgba(0,0,0,.15); }
    .usuarios th { background:var(--guinda); color:#fff; text-align:left; padding:12px; }
    .usuarios td { padding:10px 12px; border-bottom:1px solid #eee; }
    .usuarios td button { padding:8px 14px; font-size:.9rem; margin-right:6px; }
    .usuarios .error { flex-basis:100%; color:#b45309; }
  </style>

  <h2>Usuarios</h2>
  <form wire:submit="guardar">
    <strong>{{ $userId ? 'Editar usuario' : 'Nuevo usuario' }}</strong>
    <input wire:model="name" placeholder="Nombre completo">
    <input wire:model="usuario" placeholder="Usuario" autocomplete="off">
    <input wire:model="password" type="password" placeholder="{{ $userId ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" autocomplete="new-password">
    <select wire:model.live="rol">
      @foreach($roles as $r)<option value="{{ $r->value }}">{{ ucfirst($r->value) }}</option>@endforeach
    </select>
    @if($rol === 'supervisor')
      <select wire:model="direccion_id">
        <option value="">— Dirección —</option>
        @foreach($direcciones as $d)<option value="{{ $d->id }}">{{ $d->nombre }}</option>@endforeach
      </select>
    @endif
    @if($rol === 'operador')
      <select wire:model="ventanilla_id">
        <option value="">— Ventanilla —</option>
        @foreach($ventanillas as $v)<option value="{{ $v->id }}">{{ $v->area->nombre }} / {{ $v->nombre }}</option>@endforeach
      </select>
    @endif
    <button type="submit">Guardar</button>
    @if($userId)<button type="button" wire:click="cancelar">Cancelar</button>@endif
    @foreach(['name','usuario','password','rol','ventanilla_id','direccion_id'] as $c)
      @error($c)<div class="error">{{ $message }}</div>@enderror
    @endforeach
  </form>

  <table width="100%">
    <tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Ventanilla / Dirección</th><th>Estado</th><th>Sesión</th><th></th></tr>
    @foreach($usuarios as $u)
      <tr wire:key="u-{{ $u->id }}">
        <td>{{ $u->name }}</td><td>{{ $u->usuario }}</td><td>{{ $u->rol->value }}</td>
        <td>
          @if($u->rol->value === 'supervisor')
            {{ $nombresDireccion[$u->direccion_id] ?? 'Sin dirección asignada' }}
          @else
            {{ $u->ventanilla ? $u->ventanilla->area->nombre.' / '.$u->ventanilla->nombre : '—' }}
          @endif
        </td>
        <td>{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
        @php
          $ult = $u->ultima_actividad ? \Carbon\Carbon::parse($u->ultima_actividad) : null;
          $abierta = $u->sesion_id && $ult && $ult->gt(now()->subMinutes($minutosSesion));
        @endphp
        <td>{{ $abierta ? 'Abierta' : '—' }}@if($abierta) <small>(últ. actividad {{ $ult->format('H:i') }})</small>@endif</td>
        <td>
          <button wire:click="editar({{ $u->id }})">Editar</button>
          <button wire:click="toggle({{ $u->id }})">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
          @if($u->sesion_id)
            <button wire:click="liberarSesion({{ $u->id }})"
                    wire:confirm="¿Liberar la sesión de {{ $u->usuario }}? Si la está usando en este momento, se le cerrará.">Liberar sesión</button>
          @endif
        </td>
      </tr>
    @endforeach
  </table>
</div>
