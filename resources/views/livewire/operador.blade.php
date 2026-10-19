<div wire:poll.5s.keep-alive>
    @if (! $ventanilla)
        <div class="alerta">No tienes una ventanilla asignada. Pide al administrador que te la asigne.</div>
    @else
        <h2>{{ $ventanilla->area->nombre }} · {{ $ventanilla->nombre }}</h2>
        <p>{{ $enEspera }} {{ $enEspera === 1 ? 'turno en espera' : 'turnos en espera' }}</p>

        {{-- Resumen del día de este operador --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;margin:1rem 0;text-align:center">
            @foreach ([
                ['En proceso',      $resumen['proceso'],                                                        'var(--dorado)'],
                ['Atendidos',       $resumen['finalizados'],                                                    '#15803d'],
                ['No se presentó',  $resumen['ausentes'],                                                       '#b91c1c'],
                ['Total de hoy',    $resumen['proceso'] + $resumen['finalizados'] + $resumen['ausentes'],       'var(--guinda)'],
            ] as [$titulo, $cantidad, $color])
                <div style="background:#fff;border-top:4px solid {{ $color }};border-radius:8px;padding:.6rem;box-shadow:0 1px 4px rgba(0,0,0,.15)">
                    <div style="font-size:1.8rem;font-weight:700">{{ $cantidad }}</div>
                    <div style="font-size:.85rem;color:#64748b">{{ $titulo }}</div>
                </div>
            @endforeach
        </div>

        @if ($mensaje) <div class="aviso">{{ $mensaje }}</div> @endif

        @if ($turno)
            <div class="tarjeta" style="text-align:center">
                <div>Atendiendo</div>
                <div class="folio">{{ $turno->folio }}</div>
                <div>Llamadas: {{ $turno->llamadas }}</div>

                {{-- Reloj y aviso de tiempo. Comparten el mismo contador.
                     wire:ignore evita que el sondeo cada 5 s lo reinicie. --}}
                <style>
                    [x-cloak]{display:none !important}
                    .reloj-caja { background:linear-gradient(160deg,var(--guinda-osc),var(--guinda)); color:#fff; border-radius:18px;
                                  padding:1rem 1.5rem 1.1rem; margin:1.2rem auto; max-width:460px; box-shadow:0 6px 18px rgba(0,0,0,.28);
                                  transition:background .6s; }
                    .reloj-caja.ambar { background:linear-gradient(160deg,#92400e,#d97706); }
                    .reloj-caja.rojo  { background:linear-gradient(160deg,#7f1d1d,#dc2626); animation:alerta 1s ease-in-out infinite; }
                    .reloj-etq { font-size:1.2rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; display:flex; align-items:center; justify-content:center; gap:.6rem; }
                    .reloj-etq .engrane { display:inline-block; animation:giro 3s linear infinite; font-size:1.4rem; }
                    .reloj-etq .punto { width:.85rem; height:.85rem; border-radius:50%; background:#4ade80; animation:latido 1.2s ease-in-out infinite; }
                    .reloj-caja.rojo .punto, .reloj-caja.ambar .punto { background:#fff; }
                    .reloj-num { font-size:5rem; font-weight:800; line-height:1.1; letter-spacing:.05em; font-variant-numeric:tabular-nums; text-shadow:0 2px 6px rgba(0,0,0,.35); margin:.2rem 0; }
                    .reloj-barra { height:12px; background:rgba(255,255,255,.25); border-radius:99px; overflow:hidden; }
                    .reloj-barra i { display:block; height:100%; border-radius:99px; transition:width 1s linear;
                                     background:repeating-linear-gradient(45deg,var(--dorado-claro) 0 12px,var(--dorado) 12px 24px); background-size:34px 34px; animation:avanza 1s linear infinite; }
                    .reloj-pie { font-size:.95rem; opacity:.9; margin-top:.4rem; }
                    @keyframes giro    { to { transform:rotate(360deg); } }
                    @keyframes latido  { 0%,100% { transform:scale(1); opacity:1; } 50% { transform:scale(1.7); opacity:.45; } }
                    @keyframes avanza  { to { background-position:34px 0; } }
                    @keyframes alerta  { 0%,100% { box-shadow:0 0 0 0 rgba(220,38,38,.7); } 50% { box-shadow:0 0 0 16px rgba(220,38,38,0); } }
                    @media (prefers-reduced-motion: reduce) { .reloj-caja, .reloj-caja * { animation:none !important; } }
                </style>
                <div wire:ignore wire:key="reloj-{{ $turno->id }}"
                     x-data="{
                        base: {{ $transcurridoSeg }}, limite: {{ $limiteSeg }}, ext: {{ $extensionSeg }},
                        cargado: Date.now(), ahora: Date.now(), clave: 'snooze_turno_{{ $turno->id }}',
                        get seg() { return this.base + Math.floor((this.ahora - this.cargado) / 1000) },
                        get minutos() { return Math.floor(this.seg / 60) },
                        get reloj() {
                            const p = n => String(n).padStart(2, '0');
                            const h = Math.floor(this.seg / 3600), m = Math.floor((this.seg % 3600) / 60), s = this.seg % 60;
                            return (h > 0 ? h + ':' : '') + p(m) + ':' + p(s);
                        },
                        get excedido() { return this.seg >= this.limite },
                        get progreso() { return Math.min(100, Math.round(this.seg / this.limite * 100)) },
                        get silenciado() { try { return Number(localStorage.getItem(this.clave) || 0) > this.ahora } catch (e) { return false } },
                        get visible() { return this.excedido && !this.silenciado },
                        init() { setInterval(() => { this.ahora = Date.now() }, 1000) },
                        masTiempo() {
                            try { localStorage.setItem(this.clave, String(Date.now() + this.ext * 1000)) } catch (e) {}
                            this.ahora = Date.now();
                            $wire.masTiempo();
                        }
                     }">

                    <div class="reloj-caja" x-bind:class="excedido ? 'rojo' : (progreso >= 75 ? 'ambar' : '')">
                        <div class="reloj-etq"><span class="engrane">⚙</span> Tiempo de atención <span class="punto"></span></div>
                        <div class="reloj-num" x-text="reloj"></div>
                        <div class="reloj-barra"><i x-bind:style="'width:' + progreso + '%'"></i></div>
                        <div class="reloj-pie" x-text="excedido ? 'Superó el límite de ' + Math.round(limite / 60) + ' min' : 'Límite: ' + Math.round(limite / 60) + ' min'"></div>
                    </div>

                    <div x-cloak x-show="visible"
                         style="background:#fff7e0;border:3px solid var(--dorado);border-radius:12px;padding:1rem 1.2rem;margin:1rem 0">
                        <div style="font-size:1.3rem;font-weight:700;color:var(--guinda-osc)">
                            Llevas <span x-text="minutos"></span> minutos con este turno
                        </div>
                        <div style="margin:.4rem 0;font-size:1.1rem">¿Necesitas más tiempo?</div>
                        <div style="font-weight:700;color:var(--guinda);margin-bottom:.7rem">No olvides finalizar el turno.</div>
                        <button type="button" class="btn" style="background:var(--dorado)" x-on:click="masTiempo()">Necesito más tiempo</button>
                        <button type="button" class="btn verde" wire:click="finalizar">Finalizar turno</button>
                    </div>
                </div>

                <div style="display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; margin-top:1.25rem">
                    <button class="btn gris" wire:click="rellamar" wire:loading.attr="disabled">Volver a llamar</button>
                    <button class="btn verde" wire:click="finalizarYSiguiente" wire:loading.attr="disabled">Finalizar y llamar siguiente</button>
                    <button class="btn" wire:click="finalizar" wire:loading.attr="disabled">Solo finalizar</button>
                    <button class="btn rojo" wire:click="finalizar(true)"
                            wire:confirm="¿Marcar que el contribuyente no se presentó?">No se presentó</button>
                </div>
            </div>
        @else
            <div class="tarjeta" style="text-align:center">
                <button class="btn verde" style="font-size:1.5rem; padding:1.5rem 3rem"
                        wire:click="llamar" wire:loading.attr="disabled">Llamar siguiente</button>
            </div>
        @endif
    @endif
</div>
