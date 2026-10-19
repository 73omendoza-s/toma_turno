<div class="banners-admin">
  <style>
    .banners-admin h2 { color:#611232; }
    .banners-admin .caja { background:#fff; border-top:4px solid #9B2247; border-radius:8px; padding:18px; box-shadow:0 1px 4px rgba(0,0,0,.15); margin-bottom:18px; }
    .banners-admin label { display:block; font-weight:600; margin:10px 0 3px; color:#611232; }
    .banners-admin small { color:#64748b; }
    .banners-admin input[type=text], .banners-admin select { width:100%; max-width:560px; font-size:1.05rem; padding:10px 12px; }
    .banners-admin .fila2 { display:flex; gap:18px; flex-wrap:wrap; }
    .banners-admin .error { color:#b45309; display:block; }
    .banners-admin .aviso-img { background:#fff7e0; border:1px solid #A57F2C; border-radius:6px; padding:6px 10px; margin-top:6px; }
    .banners-admin .previa { margin-top:8px; max-width:100%; border:2px solid #A57F2C; border-radius:8px; }
    .banners-admin td img { height:44px; border-radius:4px; border:1px solid #cbd5e1; }
    .banners-admin .inactivo { color:#64748b; }
    .banners-admin .peligro { background:#b91c1c !important; color:#fff !important; border:0; border-radius:6px; padding:.45rem .8rem; font-size:.95rem; cursor:pointer; }
    .banners-admin .impar { background:#fff7e0; border-left:5px solid #A57F2C; padding:8px 12px; margin-bottom:14px; border-radius:6px; }
  </style>

  <h2>Banners del televisor</h2>

  @if($activos % 2 === 1)
    <div class="impar">Hay {{ $activos }} banners activos (número impar). La pantalla los muestra de dos en dos, así que uno aparecerá desfasado. Conviene dejar un número par.</div>
  @endif

  <div class="caja">
    <form wire:submit="guardar" style="border:0;padding:0;margin:0;background:transparent">
      <strong>{{ $bannerId ? 'Editar banner' : 'Nuevo banner' }}</strong>

      <label for="img">Imagen {{ $bannerId ? '(déjala vacía para conservar la actual)' : '' }}</label>
      <input id="img" type="file" wire:model="imagen" accept=".jpg,.jpeg,.png,.webp">
      <div wire:loading wire:target="imagen"><small>Cargando imagen…</small></div>
      <small style="display:block">Recomendado: 1880 × 340 px, JPG o PNG, hasta 4 MB. Si la imagen ya trae su texto, deja el título vacío.</small>
      @error('imagen') <span class="error">{{ $message }}</span> @enderror
      @if($aviso) <div class="aviso-img">{{ $aviso }}</div> @endif
      @if($imagen && $imagen->isPreviewable()) <img class="previa" src="{{ $imagen->temporaryUrl() }}" alt="Vista previa"> @endif

      <label for="tit">Título (opcional)</label>
      <input id="tit" type="text" wire:model="titulo" maxlength="80" placeholder="Visor Urbano">
      @error('titulo') <span class="error">{{ $message }}</span> @enderror

      <label for="sub">Subtítulo (opcional)</label>
      <input id="sub" type="text" wire:model="subtitulo" maxlength="160" placeholder="Catastro y adeudo predial en visorurbano.lapaz.gob.mx">
      @error('subtitulo') <span class="error">{{ $message }}</span> @enderror

      <div class="fila2">
        <div>
          <label for="anc">Ancho del texto (% del banner)</label>
          <input id="anc" type="number" min="20" max="90" wire:model="ancho" style="width:120px">
          <small style="display:block">El resto queda libre para la foto.</small>
          @error('ancho') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="aju">Ajuste de la imagen</label>
          <select id="aju" wire:model="ajuste" style="width:auto">
            <option value="cubrir">Cubrir (llena el recuadro y recorta)</option>
            <option value="contener">Contener (se ve completa)</option>
          </select>
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:8px;font-weight:400">
        <input type="checkbox" wire:model="activo"> Mostrar en la pantalla
      </label>

      <div style="margin-top:14px">
        <button type="submit" wire:loading.attr="disabled" wire:target="guardar,imagen">{{ $bannerId ? 'Guardar cambios' : 'Agregar banner' }}</button>
        @if($bannerId)<button type="button" wire:click="cancelar">Cancelar</button>@endif
      </div>
    </form>
  </div>

  <table>
    <tr><th>#</th><th>Imagen</th><th>Título</th><th>Subtítulo</th><th>Ajuste</th><th>Estado</th><th></th></tr>
    @forelse($banners as $b)
      <tr wire:key="b-{{ $b->id }}" class="{{ $b->activo ? '' : 'inactivo' }}">
        <td>{{ $loop->iteration }}</td>
        <td><img src="{{ $b->url }}" alt=""></td>
        <td>{{ $b->titulo ?: '—' }}</td>
        <td>{{ $b->subtitulo ?: '—' }}</td>
        <td>{{ $b->ajuste === 'cubrir' ? 'Cubrir' : 'Contener' }} · {{ $b->ancho }}%</td>
        <td>{{ $b->activo ? 'Activo' : 'Inactivo' }}</td>
        <td style="white-space:nowrap">
          <button type="button" wire:click="mover({{ $b->id }}, 'arriba')" @disabled($loop->first)>↑</button>
          <button type="button" wire:click="mover({{ $b->id }}, 'abajo')" @disabled($loop->last)>↓</button>
          <button type="button" wire:click="editar({{ $b->id }})">Editar</button>
          <button type="button" wire:click="toggle({{ $b->id }})">{{ $b->activo ? 'Desactivar' : 'Activar' }}</button>
          <button type="button" class="peligro" wire:click="eliminar({{ $b->id }})"
                  wire:confirm="¿Eliminar este banner? Se borra también su imagen y no se puede deshacer.">Eliminar</button>
        </td>
      </tr>
    @empty
      <tr><td colspan="7">Aún no hay banners. Agrega el primero arriba.</td></tr>
    @endforelse
  </table>
</div>
