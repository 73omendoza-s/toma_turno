<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control de turnos' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; margin: 0; color: #0f172a; }
        header { background: #9B2247; border-bottom: 4px solid #A57F2C; color: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
        header form { margin: 0; }
        header button { background: #611232; color: #fff; border: 1px solid #fff5; padding: .4rem .9rem; border-radius: 6px; cursor: pointer; }
        header button:hover { background: #4a0e26; }
        header nav { display: flex; gap: 1rem; flex: 1; flex-wrap: wrap; }
        header nav a { color: #f5e6eb; text-decoration: none; }
        header nav a:hover { color: #fff; text-decoration: underline; }
        main { max-width: 1000px; margin: 1.5rem auto; padding: 0 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; }
        .area { background: #fff; border: 2px solid #cbd5e1; border-radius: 12px; padding: 1.25rem; cursor: pointer; text-align: left; font-size: 1.05rem; display: flex; flex-direction: column; gap: .4rem; }
        .area:hover { border-color: #9B2247; }
        .area small { color: #64748b; }
        .alerta { background: #fee2e2; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .aviso { background: #f3e4e9; color: #611232; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .btn { border: 0; border-radius: 10px; padding: 1rem 1.5rem; font-size: 1.1rem; cursor: pointer; color: #fff; background: #9B2247; }
        .btn:hover { background: #611232; }
        .btn.verde { background: #15803d; } .btn.gris { background: #64748b; } .btn.rojo { background: #b91c1c; }
        .btn:disabled { opacity: .5; }
        .tarjeta { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px #0001; margin-top: 1rem; }
        .folio { font-size: 4rem; font-weight: 700; letter-spacing: .05em; }
        #ticket { text-align: center; }

        /* Pantallas de administración (elementos HTML simples, sin clase) */
        main table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; margin-top: 1rem; }
        main th, main td { text-align: left; padding: .5rem .7rem; border-bottom: 1px solid #e2e8f0; font-size: .95rem; }
        main th { background: #9B2247; color: #fff; }
        main input:not([type=checkbox]), main select { padding: .5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; margin: .2rem .2rem .2rem 0; }
        main input:not([type=checkbox]):focus, main select:focus { outline: none; border-color: #9B2247; box-shadow: 0 0 0 3px rgba(155,34,71,.2); }
        main button:not([class]) { background: #9B2247; color: #fff; border: 0; border-radius: 6px; padding: .45rem .8rem; font-size: .95rem; cursor: pointer; }
        main button:not([class]):hover { background: #611232; }
        main form:not([class]) { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem; }

        @media print {
            body * { visibility: hidden; }
            #ticket, #ticket * { visibility: visible; }
            #ticket { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
    @livewireStyles
</head>
<body>
    @php($u = auth()->user())
    <header class="no-print">
        <strong>Control de turnos</strong>

        <nav>
            @if($u->rol === \App\Enums\Rol::Admin)
                <a href="{{ url('/admin/estadisticas') }}">Estadísticas</a>
                <a href="{{ url('/admin/catalogo') }}">Áreas y ventanillas</a>
                <a href="{{ url('/admin/usuarios') }}">Usuarios</a>
                <a href="{{ url('/admin/configuracion') }}">Configuración</a>
                <a href="{{ url('/admin/banners') }}">Banners</a>
                <a href="{{ url('/recepcion') }}">Recepción</a>
            @elseif($u->rol->value === 'supervisor')
                <a href="{{ url('/admin/estadisticas') }}">Estadísticas</a>
            @elseif($u->rol === \App\Enums\Rol::Recepcion)
                <a href="{{ url('/recepcion') }}">Recepción</a>
            @elseif($u->rol === \App\Enums\Rol::Operador)
                <a href="{{ url('/operador') }}">Mi ventanilla</a>
            @endif

            @if(Route::has('pantalla') && $u->rol === \App\Enums\Rol::Admin)
                <a href="{{ route('pantalla') }}" target="_blank">Pantalla de sala</a>
            @endif
        </nav>

        <span>
            {{ $u->name }} · {{ $u->rol->etiqueta() }}
            <form method="POST" action="/logout" style="display:inline; margin-left:1rem">
                @csrf
                <button type="submit">Salir</button>
            </form>
        </span>
    </header>
    <main>{{ $slot }}</main>
    @livewireScripts
</body>
</html>
