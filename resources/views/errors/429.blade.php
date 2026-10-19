<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesión ya abierta</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem;
               font-family: system-ui, "Segoe UI", sans-serif; background: #f1f5f9; color: #0f172a; }
        .caja { background: #fff; max-width: 520px; width: 100%; border-top: 6px solid var(--guinda, #9B2247); border-radius: 12px;
                padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,.15); text-align: center; }
        h1 { margin: 0 0 .6rem; font-size: 1.6rem; color: var(--guinda-osc, #611232); }
        p { margin: .5rem 0; line-height: 1.45; }
        .cuenta { font-size: 2.2rem; font-weight: 700; color: var(--guinda, #9B2247); margin: .8rem 0; }
        .btn { display: inline-block; margin: .4rem .3rem 0; padding: .7rem 1.4rem; border: 0; border-radius: 8px; font-size: 1rem;
               background: var(--guinda, #9B2247); color: #fff; text-decoration: none; cursor: pointer; }
        .btn:hover { filter: brightness(.85); }
        small { color: #64748b; }
    </style>
</head>
<body>
    @php $espera = (int) ($exception->getHeaders()['Retry-After'] ?? 60); @endphp
    <div class="caja">
        <h1>Ya hay una sesión abierta</h1>
        <p>Desde este equipo se hicieron demasiadas solicitudes o intentos de acceso en poco tiempo. Es posible que ya tengas una sesión abierta del sistema en otra ventana o pestaña.</p>
        <p>Cierra las demás ventanas del sistema y espera para volver a intentar.</p>
        <div class="cuenta" id="cuenta">{{ $espera }}</div>
        <small>segundos para reintentar</small><br>
        <a class="btn" href="/">Volver al inicio</a>
        <button class="btn" type="button" onclick="location.reload()">Reintentar</button>
    </div>
    <script>
        let s = {{ $espera }};
        const c = document.getElementById('cuenta');
        const t = setInterval(() => { s--; if (s <= 0) { clearInterval(t); c.textContent = '0'; location.href = '/'; } else { c.textContent = s; } }, 1000);
    </script>
</body>
</html>