<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Turnos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md overflow-hidden">

        <div class="bg-guinda px-6 py-5 text-center">
            <h1 class="text-white font-black text-xl uppercase tracking-wider">Sistema de Turnos</h1>
            <p class="text-red-200 text-sm mt-1">Atención al Contribuyente</p>
        </div>

        <div class="p-6 space-y-3">
            <a href="{{ route('turno.index') }}"
               class="bg-guinda block w-full text-center text-white font-bold py-3 px-4 rounded-lg text-sm">
                Recepción — Generar turno
            </a>
            <a href="{{ route('cajero.login') }}"
               class="block w-full text-center border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-3 px-4 rounded-lg text-sm">
                Acceso de ventanilla
            </a>
        </div>

        <div class="bg-gray-50 px-6 py-3 text-center">
            <p class="text-gray-400 text-xs">H. Ayuntamiento de La Paz — Tesorería General Municipal</p>
        </div>

    </div>

</body>
</html>
