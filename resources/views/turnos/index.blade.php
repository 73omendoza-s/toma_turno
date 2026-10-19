<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
        .text-guinda { color: #6B0F2B; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-lg w-full max-w-2xl overflow-hidden">

        <div class="bg-guinda px-6 py-5 text-center">
            <h1 class="text-white font-black text-xl uppercase tracking-wider">Sistema de Turnos</h1>
            <p class="text-red-200 text-sm mt-1">Selecciona el servicio que necesita el contribuyente</p>
        </div>

        <div class="p-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($servicios->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg px-4 py-3 text-sm">
                    No hay ventanillas disponibles en este momento.
                </div>
            @else
                <div class="grid grid-cols-2 gap-3">
                    @foreach($servicios as $s)
                        <form action="{{ route('turno.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="servicio" value="{{ $s->nombre }}">
                            <button type="submit"
                                class="w-full text-left border-2 border-gray-200 hover:border-red-900 hover:bg-red-50 rounded-xl p-4 transition duration-200">
                                <span class="block text-3xl font-black text-guinda">{{ $s->prefijo }}</span>
                                <span class="block text-sm font-bold text-gray-700 mt-1">{{ $s->nombre }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <script>
        // Evita turnos duplicados por doble clic
        let enviando = false;
        document.querySelectorAll('form').forEach(f => f.addEventListener('submit', e => {
            if (enviando) { e.preventDefault(); return; }
            enviando = true;
        }));
        window.addEventListener('pageshow', () => { enviando = false; });
    </script>

</body>
</html>
