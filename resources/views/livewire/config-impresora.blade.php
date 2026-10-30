<div class="tarjeta" style="max-width:680px;margin:1rem auto">
    <h2 style="margin-top:0">Impresora térmica</h2>

    <label style="display:block;font-weight:700;margin-bottom:.4rem">1. ¿Con qué impresora van a trabajar?</label>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:.7rem">
        @foreach ($modelos as $clave => $m)
            <label style="cursor:pointer;padding:1rem;border-radius:12px;border:2px solid {{ $modelo === $clave ? '#0b6b5c' : '#c9ced6' }};background:{{ $modelo === $clave ? '#e7f3f0' : 'transparent' }}">
                <input type="radio" wire:model.live="modelo" value="{{ $clave }}" style="margin-right:.4rem">
                <strong>{{ $m['nombre'] }}</strong>
                <div class="gris" style="font-size:.85rem;margin-top:.3rem">Red: {{ $m['red'] }}</div>
            </label>
        @endforeach
    </div>

    <label style="display:block;font-weight:700;margin:1.2rem 0 .4rem">2. ¿Cómo está conectada?</label>
    <select wire:model.live="conexion" style="width:100%">
        <option value="windows">USB (impresora compartida en Windows)</option>
        <option value="network">Red (IP, puerto 9100)</option>
        <option value="archivo">Prueba sin impresora (guarda un archivo)</option>
    </select>

    @if ($conexion === 'windows')
        <label style="display:block;font-weight:700;margin:1.2rem 0 .4rem">3. Seleccione la impresora de esta PC</label>
        @if (count($detectadas))
            <select wire:change="elegir($event.target.value)" style="width:100%">
                <option value="">— Elija una impresora compartida —</option>
                @foreach ($detectadas as $n)
                    <option value="{{ $n }}" @selected($destino === $n)>{{ $n }}</option>
                @endforeach
            </select>
        @else
            <p class="gris" style="margin:.2rem 0">No se detectaron impresoras compartidas. Escriba el nombre abajo.</p>
        @endif
        <input type="text" wire:model="destino" style="width:100%;margin-top:.5rem" placeholder="{{ $modelos[$modelo]['compartida'] }}">
        <p style="margin:.4rem 0">
            <button type="button" class="btn sec" wire:click="detectar" wire:loading.attr="disabled" wire:target="detectar">Buscar de nuevo</button>
        </p>
        @error('destino') <div style="color:#b42318">{{ $message }}</div> @enderror
    @elseif ($conexion === 'network')
        <label style="display:block;font-weight:700;margin:1.2rem 0 .4rem">3. Dirección IP de la impresora</label>
        <input type="text" wire:model="destino" style="width:100%" placeholder="192.168.1.50">
        @error('destino') <div style="color:#b42318">{{ $message }}</div> @enderror
    @endif

    <div style="margin-top:1.2rem">
        <label><input type="checkbox" wire:model="directa"> Impresión directa (sin diálogo del navegador)</label><br>
        <label><input type="checkbox" wire:model="corte"> Cortar el papel al final del ticket</label>
    </div>

    @if ($mensaje)
        <p style="margin-top:1rem;padding:.6rem;border-radius:8px;{{ $tipoMensaje === 'ok' ? 'background:#e7f3f0;color:#0b6b5c' : 'background:#fde8e6;color:#b42318' }}">{{ $mensaje }}</p>
    @endif

    <p style="margin-top:1rem">
        <button type="button" class="btn grande" wire:click="guardar">Guardar</button>
        <button type="button" class="btn grande sec" wire:click="probarConexion" wire:loading.attr="disabled" wire:target="probarConexion">Probar conexión</button>
        <button type="button" class="btn grande sec" wire:click="imprimirPrueba" wire:loading.attr="disabled" wire:target="imprimirPrueba">Imprimir prueba</button>
    </p>
</div>
