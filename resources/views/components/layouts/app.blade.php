<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control de turnos' }}</title>
    <style>{!! \App\Support\Institucional::css() !!}</style>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; margin: 0; color: #0f172a; }
        header { background: var(--guinda); border-bottom: 4px solid var(--dorado); color: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
        header form { margin: 0; }
        header button { background: var(--guinda-osc); color: #fff; border: 1px solid #fff5; padding: .4rem .9rem; border-radius: 6px; cursor: pointer; }
        header button:hover { filter: brightness(.85); }
        header nav { display: flex; gap: 1rem; flex: 1; flex-wrap: wrap; }
        header nav a { color: #f5e6eb; text-decoration: none; }
        header nav a:hover { color: #fff; text-decoration: underline; }
        main { max-width: 1000px; margin: 1.5rem auto; padding: 0 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; }
        .area { background: #fff; border: 2px solid #cbd5e1; border-radius: 12px; padding: 1.25rem; cursor: pointer; text-align: left; font-size: 1.05rem; display: flex; flex-direction: column; gap: .4rem; }
        .area:hover { border-color: var(--guinda); }
        .area small { color: #64748b; }
        .alerta { background: #fee2e2; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .aviso { background: color-mix(in srgb, var(--guinda) 10%, white); color: var(--guinda-osc); padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .btn { border: 0; border-radius: 10px; padding: 1rem 1.5rem; font-size: 1.1rem; cursor: pointer; color: #fff; background: var(--guinda); }
        .btn:hover { background: var(--guinda-osc); }
        .btn.verde { background: #15803d; } .btn.gris { background: #64748b; } .btn.rojo { background: #b91c1c; }
        .btn:disabled { opacity: .5; }
        .tarjeta { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px #0001; margin-top: 1rem; }
        .folio { font-size: 4rem; font-weight: 700; letter-spacing: .05em; }
        #ticket { text-align: center; }

        /* Pantallas de administración (elementos HTML simples, sin clase) */
        main table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; margin-top: 1rem; }
        main th, main td { text-align: left; padding: .5rem .7rem; border-bottom: 1px solid #e2e8f0; font-size: .95rem; }
        main th { background: var(--guinda); color: #fff; }
        main input:not([type=checkbox]), main select { padding: .5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; margin: .2rem .2rem .2rem 0; }
        main input:not([type=checkbox]):focus, main select:focus { outline: none; border-color: var(--guinda); box-shadow: 0 0 0 3px rgba(var(--guinda-rgb), .2); }
        main button:not([class]) { background: var(--guinda); color: #fff; border: 0; border-radius: 6px; padding: .45rem .8rem; font-size: .95rem; cursor: pointer; }
        main button:not([class]):hover { background: var(--guinda-osc); }
        main form:not([class]) { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem; }


        /* ---------- Menú lateral del administrador ---------- */
        [x-cloak] { display: none !important; }
        .shell { display: flex; align-items: stretch; }
        .shell main { flex: 1; min-width: 0; }
        .menu-lateral { width: 240px; flex: 0 0 240px; background: #fff; border-right: 1px solid #e2e8f0; padding: .8rem .6rem;
                        min-height: calc(100vh - 64px); }
        .menu-lateral .titulo { font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin: .6rem .9rem .4rem; }
        .menu-lateral a { display: flex; align-items: center; gap: .85rem; padding: .7rem .9rem; margin-bottom: .2rem; border-radius: 10px;
                          color: #334155; text-decoration: none; font-weight: 600; }
        .menu-lateral a:hover { background: color-mix(in srgb, var(--guinda) 10%, white); color: var(--guinda-osc); }
        .menu-lateral a.activo { background: var(--guinda); color: #fff; }
        .menu-lateral svg, .btn-menu svg { width: 22px; height: 22px; flex: 0 0 22px; fill: none; stroke: currentColor;
                                           stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-menu { display: inline-flex; align-items: center; justify-content: center; padding: .35rem .5rem !important; }
        @media (max-width: 800px) {
            .menu-lateral { position: fixed; z-index: 50; top: 0; bottom: 0; left: 0; box-shadow: 4px 0 14px rgba(0,0,0,.25); padding-top: 4rem; }
        }

        @media print {
            body * { visibility: hidden; }
            #ticket, #ticket * { visibility: visible; }
            #ticket { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
    @livewireStyles
</head>
<body x-data="{
        abierto: (() => { try { const g = localStorage.getItem('menu_abierto'); return g === null ? window.innerWidth > 800 : g === '1'; } catch (e) { return true; } })(),
        alternar() { this.abierto = !this.abierto; try { localStorage.setItem('menu_abierto', this.abierto ? '1' : '0'); } catch (e) {} }
      }">
    @php
        $u = auth()->user();
        $esAdmin = $u->rol === \App\Enums\Rol::Admin;

        // Iconos (SVG de trazo) del menú del administrador
        $iconos = [
            'barras'   => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
            'edificio' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
            'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'ajustes'  => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
            'imagen'   => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'paleta'   => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
            'impresora' => '<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
            'ticket'   => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>',
            'pantalla' => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>',
        ];

        $menuAdmin = [
            ['/admin/estadisticas',  'admin/estadisticas*',  'Estadísticas',            'barras'],
            ['/admin/catalogo',      'admin/catalogo*',      'Direcciones y áreas',     'edificio'],
            ['/admin/usuarios',      'admin/usuarios*',      'Usuarios',                'usuarios'],
            ['/admin/configuracion', 'admin/configuracion*', 'Configuración',           'ajustes'],
            ['/admin/banners',       'admin/banners*',       'Banners',                 'imagen'],
            ['/admin/institucional', 'admin/institucional*', 'Identidad institucional', 'paleta'],
            ['/admin/impresora',     'admin/impresora*',     'Impresora térmica',       'impresora'],
            ['/recepcion',           'recepcion*',           'Recepción',               'ticket'],
        ];
    @endphp

    <header class="no-print">
        <span style="display:flex;align-items:center;gap:.8rem">
            @if($esAdmin)
                {{-- Clic: oculta o muestra el menú lateral --}}
                <button type="button" class="btn-menu" x-on:click="alternar()" title="Mostrar u ocultar el menú" aria-label="Mostrar u ocultar el menú">
                    <svg viewBox="0 0 24 24"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
            @endif
            <strong>Control de turnos</strong>
        </span>

        @unless($esAdmin)
            <nav>
                @if($u->rol->value === 'supervisor')
                    <a href="{{ url('/admin/estadisticas') }}">Estadísticas</a>
                @elseif($u->rol === \App\Enums\Rol::Recepcion)
                    <a href="{{ url('/recepcion') }}">Recepción</a>
                @elseif($u->rol === \App\Enums\Rol::Operador)
                    <a href="{{ url('/operador') }}">Mi ventanilla</a>
                @endif
            </nav>
        @else
            <nav></nav>
        @endunless

        <span>
            {{ $u->name }} · {{ $u->rol->etiqueta() }}
            <form method="POST" action="/logout" style="display:inline; margin-left:1rem">
                @csrf
                <button type="submit">Salir</button>
            </form>
        </span>
    </header>

    <div class="shell">
        @if($esAdmin)
            <aside class="menu-lateral no-print" x-show="abierto" x-cloak x-transition.opacity>
                <div class="titulo">Administración</div>
                @foreach($menuAdmin as [$ruta, $patron, $texto, $icono])
                    <a href="{{ url($ruta) }}" class="{{ request()->is($patron) ? 'activo' : '' }}">
                        <svg viewBox="0 0 24 24">{!! $iconos[$icono] !!}</svg><span>{{ $texto }}</span>
                    </a>
                @endforeach
                @if(Route::has('pantalla'))
                    <div class="titulo">Sala</div>
                    <a href="{{ route('pantalla') }}" target="_blank">
                        <svg viewBox="0 0 24 24">{!! $iconos['pantalla'] !!}</svg><span>Pantalla de sala</span>
                    </a>
                @endif
            </aside>
        @endif

        <main>{{ $slot }}</main>
    </div>

    {{-- Aviso de desconexión: revisa cada 20 s que la sesión y la conexión sigan vivas (y mantiene la sesión activa) --}}
    <div id="aviso-conexion" class="no-print" style="display:none;position:fixed;left:0;right:0;top:0;z-index:9999;padding:.9rem 1rem;text-align:center;font-weight:700;color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>
    <script>
        (function () {
            const barra = document.getElementById('aviso-conexion');
            let fallos = 0, cerrada = false;

            function mostrar(texto, color, conEnlace) {
                barra.style.background = color;
                barra.textContent = texto + ' ';
                if (conEnlace) {
                    const a = document.createElement('a');
                    a.href = '/login'; a.textContent = 'Iniciar sesión';
                    a.style.cssText = 'color:#fff;text-decoration:underline;margin-left:.6rem';
                    barra.append(a);
                }
                barra.style.display = 'block';
            }

            async function latido() {
                if (cerrada) return;
                try {
                    const r = await fetch("{{ route('sesion.estado') }}", { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' });
                    if (r.status === 401 || r.status === 419 || r.status === 409) {
                        cerrada = true;
                        mostrar('⚠ Estás desconectado: tu sesión se cerró (la liberó el administrador, se abrió tu usuario en otro equipo o expiró).', '#b91c1c', true);
                        return;
                    }
                    if (!r.ok) throw new Error('http ' + r.status);
                    if (fallos > 0) { fallos = 0; barra.style.display = 'none'; }
                } catch (e) {
                    fallos++;
                    if (fallos >= 2) mostrar('⚠ Sin conexión con el servidor. Reintentando… la pantalla no se actualiza hasta que vuelva la conexión.', '#b45309', false);
                }
            }

            setInterval(latido, 20000);
            window.addEventListener('offline', () => { fallos = 2; mostrar('⚠ Sin conexión a la red. Reintentando…', '#b45309', false); });
            window.addEventListener('online', latido);
        })();
    </script>
    @livewireScripts
</body>
</html>
