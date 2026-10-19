<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor - Estadísticas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
        .text-guinda { color: #6B0F2B; }
    </style>
</head>
<body class="min-h-screen text-gray-800" style="background:#f3f4f6;">

    {{-- Header --}}
    <div class="bg-guinda px-8 py-4 flex justify-between items-center shadow-lg">
        <div>
            <h1 class="text-xl font-black uppercase tracking-wider text-white">MUNICIPIO DE LA PAZ</h1>
            <p class="text-white text-sm font-semibold tracking-wider">PANEL DE SUPERVISOR</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-white text-sm">{{ now()->format('d/m/Y H:i') }}</span>


                    <a href="{{ route('supervisor.cajero.create') }}"
            class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-lg transition duration-200">
                + Nuevo Cajero
            </a>

            <form action="{{ route('supervisor.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-lg transition duration-200">
                    Cerrar Sesión
                </button>
            </form>


        </div>
    </div>

    <div class="p-6">

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tarjetas de resumen --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Total Atendidos</p>
                <span class="text-4xl font-black text-guinda">{{ $totalAtendidos }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Pendientes</p>
                <span class="text-4xl font-black text-yellow-500">{{ $totalPendientes }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Cancelados</p>
                <span class="text-4xl font-black text-red-400">{{ $totalCancelados }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Tiempo Promedio</p>
                <span class="text-4xl font-black text-green-500">
                    {{ $tiempoPromedio ? round($tiempoPromedio) . 'min' : '--' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">

            {{-- Ventanillas --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-base font-black text-guinda uppercase tracking-widest">Ventanillas</h2>
                </div>




                {{-- Formulario nueva ventanilla --}}

                
                <form action="{{ route('supervisor.ventanilla.store') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="flex gap-2">
                        <select name="servicio"
                            class="flex-1 border border-gray-300 rounded-lg p-1 text-xs focus:outline-none focus:ring-2 focus:ring-red-900">
                            <option value="">-- Servicio --</option>
                            <option value="Caja">Caja</option>
                            <option value="Giros Comerciales">Giros Comerciales</option>
                            <option value="Espectáculo Público">Espectáculo Público</option>
                            <option value="Subdirección de Recaudación">Subdirección de Recaudación</option>
                            <option value="Desplegados de Impuesto Predial">Desplegados de Impuesto Predial</option>
                        </select>
                        <button type="submit"
                            class="text-white font-bold py-1 px-3 rounded-lg text-xs transition duration-200 whitespace-nowrap"
                            style="background: linear-gradient(135deg, #6B0F2B, #8B1A3A);">
                            + Agregar
                        </button>
                    </div>
                </form>
                
        


                {{-- Lista de ventanillas --}}
                @foreach($ventanillas as $ventanilla)
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-bold text-gray-700">{{ $ventanilla->nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $ventanilla->servicio }}</p>
                        </div>
                        <div class="flex gap-1">
                            <form action="{{ route('supervisor.ventanilla.toggle', $ventanilla->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-xs px-2 py-1 rounded font-medium {{ $ventanilla->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $ventanilla->activo ? 'Activa' : 'Inactiva' }}
                                </button>
                            </form>
                            <form action="{{ route('supervisor.ventanilla.destroy', $ventanilla->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded font-medium">
                                    X
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- Tabla de cajeros --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Cajeros</h2>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-100">
                            <th class="text-left py-2 text-gray-400 font-medium">Nombre</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Ventanilla</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Estado</th>
                            <th class="text-left py-2 text-gray-400 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cajeros as $cajero)
                        <tr class="border-b border-gray-50">
                            <td class="py-2">
                                <p class="font-bold text-xs">{{ $cajero->nombre }}</p>
                                <p class="text-gray-400 text-xs">{{ $cajero->usuario }}</p>
                            </td>
                            <td class="py-2 text-gray-500 text-xs">
                                {{ $cajero->ventanilla->nombre ?? 'Sin asignar' }}
                            </td>
                            <td class="py-2">
                                @if($cajero->en_sesion)
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">
                                        Activo
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full font-medium">
                                        Libre
                                    </span>
                                @endif
                            </td>
                            <td class="py-2">
                                <div class="flex gap-1">
                                    <a href="{{ route('supervisor.cajero.edit', $cajero->id) }}"
                                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded font-medium">
                                        Editar
                                    </a>
                                    @if($cajero->en_sesion)
                                        <form action="{{ route('supervisor.cajero.cerrarSesion', $cajero->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded font-medium">
                                                Cerrar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Gráfica --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">
                Turnos por Cajero
            </h2>
            <canvas id="graficaCajeros"></canvas>
        </div>

            {{-- Contenido multimedia --}}
            <div class="col-span-3 bg-white rounded-2xl shadow p-6 mt-4">
                <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">
                    Contenido Pantalla TV
                </h2>

                <div class="grid grid-cols-2 gap-6">

                    {{-- Formulario subir --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-3">Sube una imagen o video para mostrar en la pantalla TV.</p>
                        <form action="{{ route('supervisor.contenido.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-bold text-gray-600 mb-1">Selecciona archivo</label>
                                <input type="file" name="archivo" accept="image/*,video/*"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900">
                                <p class="text-gray-400 text-xs mt-1">Imágenes: jpg, png, gif — Videos: mp4, avi, mov — Máx 50MB</p>
                            </div>
                            <button type="submit"
                                class="w-full text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200"
                                style="background: linear-gradient(135deg, #6B0F2B, #8B1A3A);">
                                Subir Contenido
                            </button>
                        </form>
                    </div>

                    {{-- Lista de contenidos --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-3">Contenidos subidos:</p>
                        @forelse($contenidos as $contenido)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    @if($contenido->tipo == 'imagen')
                                        <img src="{{ asset('storage/' . $contenido->archivo) }}"
                                            class="w-12 h-12 object-cover rounded-lg">
                                    @else
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <span class="text-xs text-gray-500">VIDEO</span>
                                        </div>
                                    @endif

                                   <div>
                                        <p class="text-sm font-bold text-gray-700">{{ ucfirst($contenido->tipo) }}</p>
                                        <p class="text-xs text-gray-400">{{ basename($contenido->archivo) }}</p>
                                        <form action="{{ route('supervisor.contenido.toggle', $contenido->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs px-2 py-1 rounded-full font-medium {{ $contenido->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $contenido->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </div> 


                                </div>
                                <form action="{{ route('supervisor.contenido.destroy', $contenido->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded font-medium">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-300 text-sm text-center mt-4">Sin contenido subido</p>
                        @endforelse
                    </div>

                </div>
            </div>



        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        const ctx = document.getElementById('graficaCajeros').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($cajeros->pluck('nombre')) !!},
                datasets: [{
                    label: 'Turnos atendidos',
                    data: {!! json_encode($cajeros->pluck('atendidos')) !!},
                    backgroundColor: '#6B0F2B',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>

</body>
</html>