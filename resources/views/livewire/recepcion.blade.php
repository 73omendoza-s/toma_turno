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
                <button class="area" wire:click="emitir({{ $a->id }})" wire:loading.attr="disabled">
                    <strong>{{ $a->nombre }}</strong>
                    <small>{{ $esperando[$a->id] ?? 0 }} en espera</small>
                </button>
            @endforeach
        </div>
    @endforeach

    @if ($ultimo)
        <div id="ticket" class="tarjeta">
            <div>{{ $ultimo->area->nombre }}</div>
            <div class="folio">{{ $ultimo->folio }}</div>
            <div>{{ $ultimo->created_at->format('d/m/Y H:i') }}</div>
            <p>Espere a que su turno sea llamado en pantalla.</p>
            {{-- Reimprime en la térmica (ticket de 80 mm) sin diálogo si Chrome usa --kiosk-printing --}}
            <button type="button" class="btn no-print" wire:click="reimprimir" wire:loading.attr="disabled">🖨 Reimprimir ticket</button>
        </div>
    @endif

    {{-- Receptor de impresión: al emitir un turno abre el ticket en un marco invisible y lo imprime --}}
    <div x-data
         x-on:imprimir-ticket.window="
            document.getElementById('ticket-frame')?.remove();
            const f = document.createElement('iframe');
            f.id = 'ticket-frame';
            f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
            f.src = $event.detail.url;
            document.body.appendChild(f);
         "></div>
</div>
