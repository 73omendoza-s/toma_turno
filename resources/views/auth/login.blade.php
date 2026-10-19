<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control de turnos - Acceso</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        form { background: #fff; padding: 2rem; border-radius: 12px; width: 320px; box-shadow: 0 4px 16px #0002; }
        h1 { font-size: 1.25rem; margin: 0 0 1rem; }
        label { display: block; margin-top: .75rem; font-size: .9rem; }
        input[type=text], input[type=password] { width: 100%; padding: .6rem; margin-top: .25rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; margin-top: 1.25rem; padding: .7rem; background: #1d4ed8; color: #fff; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .error { color: #b91c1c; font-size: .85rem; margin-top: .5rem; }
    </style>
</head>
<body>
    <form method="POST" action="/login">
        @csrf
        <h1>Control de turnos</h1>

        <label>Usuario
            <input type="text" name="usuario" value="{{ old('usuario') }}" autofocus autocomplete="username">
        </label>
        <label>Contraseña
            <input type="password" name="password" autocomplete="current-password">
        </label>

        @error('usuario') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
