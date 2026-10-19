<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Supervisor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-lg w-80 overflow-hidden">

        {{-- Header --}}
        <div class="bg-guinda px-6 py-5 text-center">
            <h1 class="text-white font-black text-xl uppercase tracking-wider">Sistema de Turnos</h1>
            <p class="text-red-200 text-sm mt-1">Acceso de Supervisor</p>
        </div>

        {{-- Formulario --}}
        <div class="p-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('supervisor.login.post') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-600 mb-1">Usuario</label>
                    <input
                        type="text"
                        name="usuario"
                        value="{{ old('usuario') }}"
                        class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                        placeholder="Ingresa tu usuario"
                        autofocus
                    >
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-600 mb-1">Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                        placeholder="Ingresa tu contraseña"
                    >
                </div>

                <button
                    type="submit"
                    class="bg-guinda w-full text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                    Iniciar Sesión
                </button>

            </form>

        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-3 text-center">
            <p class="text-gray-400 text-xs">H. Ayuntamiento de La Paz — Subdirección de Recaudación</p>
        </div>

    </div>

</body>
</html>
