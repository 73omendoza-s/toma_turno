<div class="area" wire:key="area-{{ $a->id }}">
  <strong>{{ $a->nombre }}</strong> ({{ $a->prefijo }}) — {{ $a->activa ? 'Activa' : 'Inactiva' }}
  <button wire:click="editarArea({{ $a->id }})">Editar</button>
  <button wire:click="toggleArea({{ $a->id }})">{{ $a->activa ? 'Desactivar' : 'Activar' }}</button>
  <button wire:click="eliminarArea({{ $a->id }})"
          wire:confirm="¿Eliminar el área {{ $a->nombre }}? Esta acción no se puede deshacer."
          class="peligro"
          @disabled($a->ventanillas->isNotEmpty())
          title="{{ $a->ventanillas->isNotEmpty() ? 'Elimina primero sus ventanillas' : 'Eliminar área' }}">Eliminar</button>
  <ul>
    @foreach($a->ventanillas as $v)
      <li wire:key="vent-{{ $v->id }}" class="{{ $v->activa ? '' : 'inactiva' }}">
        <span class="{{ $ventId === $v->id ? 'editando' : '' }}">{{ $v->nombre }}</span> — {{ $v->activa ? 'Activa' : 'Inactiva' }}
        <button wire:click="editarVentanilla({{ $v->id }})">Editar</button>
        <button wire:click="toggleVentanilla({{ $v->id }})">{{ $v->activa ? 'Desactivar' : 'Activar' }}</button>
        <button wire:click="eliminarVentanilla({{ $v->id }})"
                wire:confirm="¿Eliminar la ventanilla {{ $v->nombre }}? Esta acción no se puede deshacer."
                class="peligro">Eliminar</button>
      </li>
    @endforeach
  </ul>
</div>
