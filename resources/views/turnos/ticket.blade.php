<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turno {{ $turno->numero }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: 80mm auto; margin: 0; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; margin: 0; }
            .ticket { box-shadow: none; border: none; border-radius: 0; width: 72mm; margin: 0 auto; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-start justify-center p-4">

    <div class="ticket bg-white rounded-2xl shadow-lg p-6 w-72 text-center">

        <h1 class="text-base font-bold text-gray-700 mb-1">H. Ayuntamiento de La Paz</h1>
        <p class="text-gray-500 text-xs mb-1">Tesorería General Municipal</p>
        <p class="text-gray-400 text-xs mb-3">{{ $turno->created_at->format('d/m/Y H:i:s') }}</p>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        <p class="text-gray-500 text-xs uppercase tracking-widest mb-2">Tu número de turno</p>
        <div class="bg-red-50 rounded-xl py-6 mb-4">
            <span class="text-7xl font-black" style="color: #6B0F2B;">{{ $turno->numero }}</span>
        </div>

        <div class="text-left bg-gray-50 rounded-lg p-3 mb-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Servicio</p>
            <p class="text-base font-black text-gray-800">{{ $turno->servicio }}</p>
        </div>

        <p class="text-gray-600 text-sm font-bold mb-1">Personas antes que tú: {{ $enEspera }}</p>
        <p class="text-gray-400 text-xs mb-3">Espera a que tu número aparezca en pantalla</p>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        <div class="no-print flex flex-col gap-2">
            <button onclick="window.print()"
                class="w-full text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200"
                style="background: linear-gradient(135deg, #6B0F2B, #8B1A3A);">
                Imprimir
            </button>
            <a href="{{ route('turno.index') }}"
               class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                Solicitar otro turno
            </a>
        </div>

    </div>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 500));
    </script>

</body>
</html>
