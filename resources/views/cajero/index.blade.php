<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cajero->numero_caja }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-start justify-start p-6">

    <div class="bg-white rounded-2xl shadow-lg p-10 w-full max-w-md text-center">

        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ $cajero->nombre }}</h1>
        <p class="text-lg font-bold text-gray-600 mb-1">Clave de empleado: {{ $cajero->clave_empleado }}</p>
        <p class="text-sm text-gray-500 mb-4">
            {{ $cajero->ventanilla?->nombre }} — {{ $cajero->ventanilla?->servicio }}
        </p>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex items-center justify-center w-24 h-24 rounded-full mb-6 mx-auto"
             style="background: #f9e5ea; color: #6B0F2B;">
            <span class="text-xl font-black text-center">{{ $cajero->numero_caja }}</span>
        </div>

        <div class="border-t border-dashed border-gray-300 my-4"></div>

        @if($turnoActual)
            <div class="bg-guinda rounded-2xl p-10 mb-4 shadow-lg">
                <p class="text-red-200 text-sm uppercase tracking-widest mb-2">Turno en atención</p>
                <span class="text-9xl font-black text-white drop-shadow-lg">{{ $turnoActual->numero }}</span>
                <p class="text-red-200 text-sm mt-3 uppercase tracking-widest">{{ $turnoActual->servicio }}</p>
            </div>
            <p class="text-gray-500 text-xl font-bold mb-4">
                Llamado: {{ $turnoActual->llamado_at?->format('H:i:s') }}
            </p>

            <div class="grid grid-cols-2 gap-2 mb-2">
                <form action="{{ route('cajero.volver', $cajero->numero_caja) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-3 rounded-lg text-sm transition duration-200">
                        Volver a llamar
                    </button>
                </form>
                <form action="{{ route('cajero.noPresento', $cajero->numero_caja) }}" method="POST"
                      onsubmit="return confirm('¿El contribuyente no se presentó? El turno se cancelará.')">
                    @csrf
                    <button type="submit"
                        class="w-full bg-red-50 hover:bg-red-100 text-red-800 font-bold py-2 px-3 rounded-lg text-sm transition duration-200">
                        No se presentó
                    </button>
                </form>
            </div>
        @else
            <div class="bg-gray-50 rounded-xl p-8 mb-6">
                <span class="text-4xl font-black text-gray-500">Sin turno activo</span>
            </div>
        @endif

        <div class="bg-gray-50 rounded-xl p-3 mb-4">
            <p class="text-gray-500 text-sm uppercase tracking-widest">En espera</p>
            <span class="text-3xl font-black" style="color: #6B0F2B;">{{ $pendientes }}</span>
        </div>

        <div class="border-t border-dashed border-gray-300 my-4"></div>

        <form action="{{ route('cajero.siguiente', $cajero->numero_caja) }}" method="POST"
              onsubmit="const b=this.querySelector('button'); b.disabled=true; b.textContent='Llamando...';">
            @csrf
            <button type="submit"
                class="bg-guinda w-full text-white font-bold py-4 px-6 rounded-lg text-lg transition duration-200 disabled:opacity-50">
                Siguiente Turno
            </button>
        </form>

        <div class="mt-6 bg-gray-50 rounded-xl p-4">
            <p class="text-gray-500 text-sm uppercase tracking-widest mb-1">
                Turnos atendidos hoy — {{ now()->format('d/m/Y') }}
            </p>
            <span class="text-5xl font-black" style="color: #6B0F2B;">{{ $atendidosHoy }}</span>
        </div>

        <form action="{{ route('cajero.logout') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                Cerrar Sesión
            </button>
        </form>

    </div>

</body>
</html>
