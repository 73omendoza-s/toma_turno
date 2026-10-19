<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
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
            <h1 class="text-xl font-black uppercase tracking-wider text-white">PANEL ADMINISTRADOR</h1>
            <p class="text-white text-sm font-semibold tracking-wider">Recuperación de contraseñas y bitácora</p>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-lg transition duration-200">
                Cerrar Sesión
            </button>
        </form>
    </div>

    <div class="p-6">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">

            {{-- Supervisores --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Supervisores</h2>
                @foreach($supervisores as $supervisor)
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-bold">
                                {{ $supervisor->nombre }}
                                @if(!$supervisor->activo)
                                    <span class="text-xs bg-gray-200 text-gray-600 rounded px-1">inactivo</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400">{{ $supervisor->usuario }}</p>
                        </div>
                        <form action="{{ route('admin.supervisor.reset', $supervisor->id) }}" method="POST" class="flex gap-2"
                              onsubmit="return confirm('¿Restablecer la contraseña de {{ $supervisor->usuario }}?')">
                            @csrf
                            <input type="password" name="password" placeholder="Nueva contraseña" required minlength="6" autocomplete="new-password"
                                class="border border-gray-300 rounded-lg p-1 text-xs w-32 focus:outline-none focus:ring-2 focus:ring-red-900">
                            <button type="submit"
                                class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded font-medium">
                                Restablecer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            {{-- Cajeros --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Cajeros</h2>
                @foreach($cajeros as $cajero)
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-bold">
                                {{ $cajero->nombre }}
                                @if(!$cajero->activo)
                                    <span class="text-xs bg-gray-200 text-gray-600 rounded px-1">inactivo</span>
                                @endif
                                @if($cajero->en_sesion)
                                    <span class="text-xs bg-green-100 text-green-700 rounded px-1">en sesión</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400">{{ $cajero->usuario }} — {{ $cajero->numero_caja }}</p>
                        </div>
                        <form action="{{ route('admin.cajero.reset', $cajero->id) }}" method="POST" class="flex gap-2"
                              onsubmit="return confirm('¿Restablecer la contraseña de {{ $cajero->usuario }}?')">
                            @csrf
                            <input type="password" name="password" placeholder="Nueva contraseña" required minlength="6" autocomplete="new-password"
                                class="border border-gray-300 rounded-lg p-1 text-xs w-32 focus:outline-none focus:ring-2 focus:ring-red-900">
                            <button type="submit"
                                class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded font-medium">
                                Restablecer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- Bitácora --}}
        <div class="bg-white rounded-2xl shadow p-6 mt-4 overflow-x-auto">
            <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Bitácora (últimos 30 eventos)</h2>
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left uppercase tracking-widest text-gray-400 border-b">
                        <th class="py-2">Fecha</th>
                        <th class="py-2">Rol</th>
                        <th class="py-2">Usuario</th>
                        <th class="py-2">Acción</th>
                        <th class="py-2">Detalle</th>
                        <th class="py-2">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bitacora as $b)
                        <tr class="border-b border-gray-100">
                            <td class="py-1">{{ $b->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="py-1">{{ $b->rol }}</td>
                            <td class="py-1">{{ $b->usuario_nombre }}</td>
                            <td class="py-1 font-bold">{{ $b->accion }}</td>
                            <td class="py-1 text-gray-500">{{ $b->detalle }}</td>
                            <td class="py-1 text-gray-400">{{ $b->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-3 text-center text-gray-400">Sin eventos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
