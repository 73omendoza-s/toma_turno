<div class="inst-admin">
  <style>
    .inst-admin h2 { color:var(--guinda-osc); }
    .inst-admin .caja { background:#fff; border-top:4px solid var(--guinda); border-radius:8px; padding:18px; box-shadow:0 1px 4px rgba(0,0,0,.15); margin-bottom:18px; }
    .inst-admin h3 { margin:0 0 8px; color:var(--guinda-osc); }
    .inst-admin label { display:block; font-weight:600; margin:10px 0 3px; color:var(--guinda-osc); }
    .inst-admin small { color:#64748b; }
    .inst-admin input[type=text] { width:100%; max-width:560px; font-size:1.05rem; padding:10px 12px; }
    .inst-admin .colores { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:10px 22px; }
    .inst-admin .color { display:flex; align-items:center; gap:8px; }
    .inst-admin input[type=color] { width:56px; height:42px; padding:2px; cursor:pointer; }
    .inst-admin .color input[type=text] { width:110px; font-family:monospace; }
    .inst-admin .error { color:#b45309; display:block; }
    .inst-admin .logos { display:flex; gap:28px; flex-wrap:wrap; }
    .inst-admin .logo-actual { background:repeating-conic-gradient(#e5e7eb 0 25%, #fff 0 50%) 50% / 16px 16px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; display:inline-block; }
    .inst-admin .logo-actual img { height:80px; width:auto; display:block; }
    .inst-admin .aviso-c { background:#fff7e0; border-left:5px solid var(--dorado); padding:8px 12px; margin-bottom:8px; border-radius:6px; }
    .inst-admin .ok { color:#15803d; font-weight:600; margin-left:10px; }
    .inst-admin .peligro { background:#b91c1c !important; color:#fff !important; border:0; border-radius:6px; padding:.45rem .8rem; font-size:.95rem; cursor:pointer; }
    .inst-admin .vista { border-radius:10px; overflow:hidden; border:1px solid #cbd5e1; }
    .inst-admin .vista-enc { display:flex; align-items:center; gap:14px; padding:12px 18px; color:#fff; }
    .inst-admin .vista-enc img { height:56px; width:auto; }
    .inst-admin .vista-cuerpo { padding:14px 18px; display:flex; gap:14px; align-items:center; flex-wrap:wrap; }
  </style>

  <h2>Identidad institucional</h2>
  <p><small>Los cambios se aplican a todas las pantallas: menú, operador, recepción, estadísticas, televisor y ticket. El televisor se recarga solo en menos de un minuto.</small></p>

  @foreach($avisos as $a) <div class="aviso-c">{{ $a }}</div> @endforeach

  <div class="caja">
    <h3>Vista previa</h3>
    <div class="vista">
      <div class="vista-enc" style="background:linear-gradient(180deg,{{ $col['color_primario'] }},{{ $col['color_primario_osc'] }});border-bottom:4px solid {{ $col['color_acento'] }}">
        <img src="{{ $escudo && $escudo->isPreviewable() ? $escudo->temporaryUrl() : $logoEsc }}" alt="" onerror="this.style.display='none'">
        <div style="flex:1">
          <strong style="font-size:1.2rem;text-transform:uppercase">{{ $v['inst_nombre'] ?? '' }}</strong><br>
          <em style="color:{{ $col['color_acento_claro'] }}">{{ $v['inst_subtitulo'] ?? '' }}</em>
        </div>
        <img src="{{ $secundario && $secundario->isPreviewable() ? $secundario->temporaryUrl() : $logoSec }}" alt="" onerror="this.style.display='none'">
      </div>
      <div class="vista-cuerpo" style="background:linear-gradient(160deg,{{ $col['color_crema_1'] }},{{ $col['color_crema_2'] }})">
        <span style="background:{{ $col['color_primario'] }};color:#fff;padding:8px 16px;border-radius:8px;font-weight:600">Botón</span>
        <span style="background:{{ $col['color_primario_osc'] }};color:{{ $col['color_acento_claro'] }};padding:8px 16px;border-radius:8px;font-weight:600">Texto de acento</span>
        <span style="border:2px solid {{ $col['color_acento'] }};color:{{ $col['color_primario_osc'] }};padding:6px 14px;border-radius:8px;font-weight:600">Borde de tarjeta</span>
      </div>
    </div>
  </div>

  <form wire:submit="guardar" style="border:0;padding:0;margin:0;background:transparent">
    <div class="caja">
      <h3>Textos</h3>
      @foreach(\App\Support\Institucional::TEXTOS as $k => $etq)
        <label for="t-{{ $k }}">{{ $etq }}</label>
        <input id="t-{{ $k }}" type="text" wire:model.live.debounce.400ms="v.{{ $k }}" maxlength="80">
        @error("v.$k") <span class="error">{{ $message }}</span> @enderror
      @endforeach
    </div>

    <div class="caja">
      <h3>Colores</h3>
      <div class="colores">
        @foreach(\App\Support\Institucional::COLORES as $k => $etq)
          <div>
            <label for="c-{{ $k }}">{{ $etq }}</label>
            <div class="color">
              <input id="c-{{ $k }}" type="color" wire:model.live="v.{{ $k }}">
              <input type="text" wire:model.live.debounce.500ms="v.{{ $k }}" maxlength="7" placeholder="#RRGGBB">
            </div>
            @error("v.$k") <span class="error">{{ $message }}</span> @enderror
          </div>
        @endforeach
      </div>
    </div>

    <div class="caja">
      <h3>Logos</h3>
      <small>PNG, JPG o WEBP, hasta 2 MB. Conviene un PNG con fondo transparente y colores claros, porque se muestran sobre el color principal.</small>
      <div class="logos">
        <div>
          <label>Escudo (encabezado del televisor y del ticket)</label>
          <div class="logo-actual"><img src="{{ $logoEsc }}" alt="" onerror="this.style.opacity=.2"></div>
          <input type="file" wire:model="escudo" accept=".png,.jpg,.jpeg,.webp">
          @error('escudo') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label>Logo secundario (a la derecha, antes de la hora)</label>
          <div class="logo-actual" style="background-color:var(--guinda)"><img src="{{ $logoSec }}" alt="" onerror="this.style.opacity=.2"></div>
          <input type="file" wire:model="secundario" accept=".png,.jpg,.jpeg,.webp">
          @error('secundario') <span class="error">{{ $message }}</span> @enderror
        </div>
      </div>
    </div>

    <div>
      <button type="submit" wire:loading.attr="disabled" wire:target="guardar,escudo,secundario">Guardar cambios</button>
      <button type="button" class="peligro" wire:click="restaurar"
              wire:confirm="¿Restaurar los colores, textos y logos originales? Los logos subidos se eliminan.">Restaurar valores originales</button>
      @if($guardado)<span class="ok">Guardado</span>@endif
    </div>
  </form>

  <p style="margin-top:18px"><small>Las imágenes de los banners del televisor (por ejemplo, las de Línea Púrpura o Impulso) ya traen sus colores dentro de la imagen y no cambian con este módulo: se vuelven a subir en Banners.</small></p>
</div>
