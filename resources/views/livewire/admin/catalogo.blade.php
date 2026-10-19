@php
  $dirs = $direcciones->pluck('nombre', 'id');
  $porDireccion = $areas->groupBy(fn ($a) => $a->direccion_id ?: 0);
@endphp
<div class="catalogo">
  <style>
    .catalogo h2 { color:var(--guinda-osc); }
    .catalogo h3 { color:var(--guinda-osc); margin:1.4rem 0 .4rem; }
    .catalogo .area { background:#fff; border:1px solid #e2e8f0; border-left:5px solid var(--guinda); border-radius:8px; padding:12px 14px; margin:10px 0; }
    .catalogo .area ul { margin:.6rem 0 0; padding-left:1.2rem; }
    .catalogo .area li { margin:.35rem 0; }
    .catalogo .inactiva { color:#64748b; }
    .catalogo .error { color:#b45309; }
    .catalogo .peligro { background:#b91c1c !important; color:#fff !important; border:0; border-radius:6px; padding:.45rem .8rem; font-size:.95rem; cursor:pointer; }
    .catalogo .peligro:hover { background:#7f1d1d !important; }
    .catalogo .editando { background:color-mix(in srgb, var(--guinda) 10%, white); border-radius:6px; padding:2px 6px; }
    .catalogo .dir-titulo { background:var(--guinda); color:#fff; padding:8px 14px; border-radius:8px; margin:1.4rem 0 .2rem; font-weight:700; }
    .catalogo .etq-dir { display:inline-block; background:var(--dorado); color:#fff; border-radius:12px; padding:1px 10px; font-size:.8rem; margin-left:6px; }
    .catalogo .sin-dir { background:#fff7e0; border-left:5px solid #b45309; padding:8px 12px; border-radius:6px; margin:10px 0; }
  </style>

  <h2>Direcciones, áreas y ventanillas</h2>

  {{-- ---------- Direcciones ---------- --}}
  <form wire:submit="guardarDireccion">
    <strong>{{ $dirId ? 'Editar dirección' : 'Nueva dirección' }}</strong>
    <input wire:model="dirNombre" placeholder="Dirección de Ingresos">
    <button type="submit">{{ $dirId ? 'Guardar' : 'Agregar' }}</button>
    @if($dirId)<button type="button" wire:click="cancelarDireccion">Cancelar</button>@endif
    @error('dirNombre') <span class="error">{{ $message }}</span> @enderror
    <div style="margin-top:.5rem">
      @foreach($direcciones as $d)
        <div class="{{ $d->activa ? '' : 'inactiva' }}" wire:key="dir-{{ $d->id }}" style="margin:.25rem 0">
          <b>{{ $d->nombre }}</b> — {{ $d->activa ? 'Activa' : 'Inactiva' }}
          <button type="button" wire:click="editarDireccion({{ $d->id }})">Editar</button>
          <button type="button" wire:click="toggleDireccion({{ $d->id }})">{{ $d->activa ? 'Desactivar' : 'Activar' }}</button>
        </div>
      @endforeach
    </div>
  </form>

  {{-- ---------- Áreas ---------- --}}
  <form wire:submit="guardarArea">
    <strong>{{ $areaId ? 'Editar área' : 'Nueva área' }}</strong>
    <select wire:model="areaDireccionId">
      <option value="">— Dirección —</option>
      @foreach($direcciones->where('activa', true) as $d)<option value="{{ $d->id }}">{{ $d->nombre }}</option>@endforeach
    </select>
    <input wire:model="areaNombre" placeholder="Nombre">
    <input wire:model="areaPrefijo" placeholder="Prefijo (SA)" maxlength="5">
    <button type="submit">Guardar</button>
    @if($areaId)<button type="button" wire:click="cancelarArea">Cancelar</button>@endif
    @error('areaDireccionId') <span class="error">{{ $message }}</span> @enderror
    @error('areaNombre') <span class="error">{{ $message }}</span> @enderror
    @error('areaPrefijo') <span class="error">{{ $message }}</span> @enderror
  </form>

  {{-- ---------- Ventanillas ---------- --}}
  <form wire:submit="guardarVentanilla">
    <strong>{{ $ventId ? 'Editar ventanilla' : 'Nueva ventanilla' }}</strong>
    <select wire:model="ventAreaId">
      <option value="">— Área —</option>
      @foreach($direcciones as $d)
        @if($porDireccion->has($d->id))
          <optgroup label="{{ $d->nombre }}">
            @foreach($porDireccion[$d->id] as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
          </optgroup>
        @endif
      @endforeach
      @if($porDireccion->has(0))
        <optgroup label="Sin dirección">
          @foreach($porDireccion[0] as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
        </optgroup>
      @endif
    </select>
    <input wire:model="ventNombre" placeholder="Ventanilla 3">
    <button type="submit">{{ $ventId ? 'Guardar cambios' : 'Agregar' }}</button>
    @if($ventId)<button type="button" wire:click="cancelarVentanilla">Cancelar</button>@endif
    @error('ventAreaId') <span class="error">{{ $message }}</span> @enderror
    @error('ventNombre') <span class="error">{{ $message }}</span> @enderror
  </form>

  {{-- ---------- Listado agrupado por dirección ---------- --}}
  @if($porDireccion->has(0))
    <div class="sin-dir">Hay áreas sin dirección asignada. Edítalas y elige su dirección: mientras no la tengan, solo el administrador verá sus estadísticas.</div>
  @endif

  @foreach($direcciones as $d)
    <div class="dir-titulo">{{ $d->nombre }}{{ $d->activa ? '' : ' (inactiva)' }}</div>
    @forelse($porDireccion[$d->id] ?? [] as $a)
      @include('livewire.admin.partials.catalogo-area', ['a' => $a])
    @empty
      <div class="inactiva" style="margin:.5rem 0">Esta dirección aún no tiene áreas.</div>
    @endforelse
  @endforeach

  @if($porDireccion->has(0))
    <div class="dir-titulo" style="background:#b45309">Sin dirección</div>
    @foreach($porDireccion[0] as $a)
      @include('livewire.admin.partials.catalogo-area', ['a' => $a])
    @endforeach
  @endif
</div>
