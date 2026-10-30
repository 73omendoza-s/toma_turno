<div wire:poll.10s.keep-alive>
    <h2 class="no-print">¿A dónde se dirige el contribuyente?</h2>

    @if ($error) <div class="alerta no-print">{{ $error }}</div> @endif

    @php
        $porDir = $areas->groupBy(fn ($a) => $a->direccion_id ?: 0);
        $bloques = [];
        foreach ($direcciones as $d) { if ($porDir->has($d->id)) $bloques[] = [$d->nombre, $porDir[$d->id]]; }
        if ($porDir->has(0)) $bloques[] = ['Otras áreas', $porDir[0]];
    @endphp
    @foreach ($bloques as [$tituloDir, $areasDir])
        <h3 class="no-print" style="color:var(--guinda-osc);margin:1.2rem 0 .4rem">{{ $tituloDir }}</h3>
        <div class="grid no-print">
            @foreach ($areasDir as $a)
                {{-- wire:target="emitir": se bloquea solo mientras se emite un turno (no con el sondeo de 10 s)
                     y evita el doble clic: mientras dura la petición, ningún botón de área responde --}}
                <button class="area" wire:click="emitir({{ $a->id }})" wire:loading.attr="disabled" wire:target="emitir">
                    <strong>{{ $a->nombre }}</strong>
                    <small>{{ $esperando[$a->id] ?? 0 }} en espera</small>
                </button>
            @endforeach
        </div>
    @endforeach

    @if ($ultimo)
        <div id="ticket" class="tarjeta">
            <div>{{ $ultimo->area?->nombre }}</div>
            <div class="folio">{{ $ultimo->folio }}</div>
            <div>{{ $ultimo->created_at->format('d/m/Y H:i') }}</div>
            <p>Espere a que su turno sea llamado en pantalla.</p>
            {{-- Reimprime el último ticket: directo a la térmica (IMPRESION_DIRECTA=true) o por el navegador (false) --}}
            <button type="button" class="btn no-print" wire:click="reimprimir" wire:loading.attr="disabled" wire:target="reimprimir">🖨 Reimprimir ticket</button>
        </div>
    @endif

    {{-- Receptor de impresión por navegador: solo actúa con IMPRESION_DIRECTA=false (método de respaldo).
         Con impresión directa no llega ningún evento y este bloque no hace nada. No lo borres. --}}
    <div x-data="{
        ocupado: false,
        imprimir(url) {
            console.log('imprimir-ticket', url, new Date().toISOString());
            if (this.ocupado) { console.warn('Impresión en curso: evento ignorado'); return; }
            this.ocupado = true;
            document.getElementById('ticket-frame')?.remove();
            const f = document.createElement('iframe');
            f.id = 'ticket-frame';
            f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
            f.src = url + (url.includes('?') ? '&' : '?') + 't=' + Date.now();
            document.body.appendChild(f);
            setTimeout(() => { this.ocupado = false; }, 3500);
        }
     }"
      x-on:imprimir-ticket.window="imprimir($event.detail.url)">
    </div>
</div>

