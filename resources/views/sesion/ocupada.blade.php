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
        .caja { background: #fff; max-width: 540px; width: 100%; border-top: 6px solid var(--guinda, #9B2247); border-radius: 12px;
                padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,.15); text-align: center; }
        h1 { margin: 0 0 .6rem; font-size: 1.6rem; color: var(--guinda-osc, #611232); }
        p { margin: .6rem 0; line-height: 1.45; }
        .btn { display: inline-block; margin-top: .8rem; padding: .7rem 1.4rem; border-radius: 8px; font-size: 1rem;
               background: var(--guinda, #9B2247); color: #fff; text-decoration: none; }
        .btn:hover { filter: brightness(.85); }
        small { color: #64748b; }
    </style>
</head>
<body>
    <div class="caja">
        <h1>Ya hay una sesión abierta</h1>
        <p>El usuario <b>{{ $usuario }}</b> ya tiene una sesión abierta en otro equipo o navegador.</p>
        <p>Cierra esa sesión con el botón <b>Salir</b>, o espera unos minutos sin actividad para entrar desde aquí. Si no puedes cerrarla, pide al administrador que la libere.</p>
        <a class="btn" href="/login">Volver a iniciar sesión</a>
        <p><small>Una sesión que no se usa durante unos minutos se considera cerrada.</small></p>
    </div>
</body>
</html>
