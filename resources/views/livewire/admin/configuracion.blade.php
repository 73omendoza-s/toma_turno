<div class="config">
  <style>
    .config h2 { color:var(--guinda-osc); }
    .config .caja { background:#fff; border-top:4px solid var(--guinda); border-radius:8px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.15); max-width:640px; }
    .config label { display:block; font-weight:600; margin:14px 0 4px; color:var(--guinda-osc); }
    .config small { display:block; color:#64748b; margin-bottom:6px; }
    .config input[type=number] { font-size:1.2rem; padding:12px 14px; width:140px; border:2px solid #cfcfcf; border-radius:8px; }
    .config .rapidos { margin:8px 0; }
    .config .rapidos button { margin-right:6px; }
    .config .ok { color:#15803d; font-weight:600; margin-left:10px; }
    .config .error { color:#b45309; display:block; }
  </style>

  <h2>Configuración</h2>

  <div class="caja">
    <form wire:submit="guardar" style="border:0;padding:0;margin:0;background:transparent">
      <label for="ta">Tiempo máximo de atención (minutos)</label>
      <small>Cuando un turno lleva este tiempo en atención, al operador se le avisa y se le pregunta si necesita más tiempo.</small>
      <input id="ta" type="number" min="5" max="120" wire:model="tiempoAtencion">
      <div class="rapidos">
        @foreach([15, 20, 25, 28, 30] as $m)
          <button type="button" wire:click="$set('tiempoAtencion', '{{ $m }}')">{{ $m }} min</button>
        @endforeach
      </div>
      @error('tiempoAtencion') <span class="error">{{ $message }}</span> @enderror

      <label for="te">Volver a avisar si pide más tiempo (minutos)</label>
      <small>Si el operador indica que necesita más tiempo, el aviso reaparece pasado este lapso.</small>
      <input id="te" type="number" min="1" max="30" wire:model="tiempoExtension">
      @error('tiempoExtension') <span class="error">{{ $message }}</span> @enderror

      <label for="si">Minutos sin actividad para considerar una sesión abandonada</label>
      <small>Mientras el sistema esté abierto en el navegador, la sesión se mantiene activa sola. Este tiempo solo cuenta cuando el navegador se cerró o el equipo se apagó sin pulsar Salir: pasado este lapso, ese usuario puede entrar desde otro equipo.</small>
      <input id="si" type="number" min="2" max="240" wire:model="sesionInactividad">
      @error('sesionInactividad') <span class="error">{{ $message }}</span> @enderror

      <div style="margin-top:18px">
        <button type="submit">Guardar</button>
        @if($guardado)<span class="ok">Guardado</span>@endif
      </div>
    </form>
  </div>
</div>
