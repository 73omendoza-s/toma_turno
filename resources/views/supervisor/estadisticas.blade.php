<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas por operador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
        .text-guinda { color: #6B0F2B; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

@php
    $fmt = fn ($s) => \App\Http\Controllers\SupervisorController::mmss($s);
    $totAtendidos = $porOperador->sum('atendidos');
    $totNoPresentados = $porOperador->sum('no_presentados');
@endphp

    {{-- Header --}}
    <div class="bg-guinda px-8 py-4 flex justify-between items-center shadow-lg">
        <div>
            <h1 class="text-xl font-black uppercase tracking-wider text-white">Estadísticas por operador</h1>
            <p class="text-white text-sm">Periodo: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('supervisor.index') }}"
           class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-lg transition duration-200">
            ← Regresar
        </a>
    </div>

    <div class="p-6 space-y-4">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Filtro --}}
        <div class="bg-white rounded-2xl shadow p-4">
            <form action="{{ route('supervisor.estadisticas') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}"
                        class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}"
                        class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900">
                </div>
                <button type="submit" class="bg-guinda text-white font-bold py-2 px-4 rounded-lg text-sm">Consultar</button>
                <a href="{{ route('supervisor.estadisticas.exportar', ['desde' => $desde, 'hasta' => $hasta]) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm">
                    Exportar a Excel (CSV)
                </a>
            </form>
        </div>

        {{-- Resumen --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Atendidos</p>
                <span class="text-4xl font-black text-guinda">{{ $totAtendidos }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No se presentaron</p>
                <span class="text-4xl font-black text-guinda">{{ $totNoPresentados }}</span>
            </div>
        </div>

        {{-- Por operador --}}
        <div class="bg-white rounded-2xl shadow p-6 overflow-x-auto">
            <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Por operador</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-widest text-gray-400 border-b">
                        <th class="py-2">Operador</th>
                        <th class="py-2">Ventanilla</th>
                        <th class="py-2 text-right">Atendidos</th>
                        <th class="py-2 text-right">No se presentó</th>
                        <th class="py-2 text-right">Atención prom. (mm:ss)</th>
                        <th class="py-2 text-right">Espera prom. (mm:ss)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porOperador as $f)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-bold">{{ $f->nombre }}</td>
                            <td class="py-2 text-gray-500">{{ $f->ventanilla ?? '---' }}</td>
                            <td class="py-2 text-right font-black text-guinda">{{ $f->atendidos }}</td>
                            <td class="py-2 text-right">{{ $f->no_presentados }}</td>
                            <td class="py-2 text-right">{{ $fmt($f->seg_atencion) }}</td>
                            <td class="py-2 text-right">{{ $fmt($f->seg_espera) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-400">Sin cajeros registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Por servicio --}}
        <div class="bg-white rounded-2xl shadow p-6 overflow-x-auto">
            <h2 class="text-base font-black text-guinda uppercase tracking-widest mb-4">Por servicio</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-widest text-gray-400 border-b">
                        <th class="py-2">Servicio</th>
                        <th class="py-2 text-right">Turnos</th>
                        <th class="py-2 text-right">Atendidos</th>
                        <th class="py-2 text-right">Cancelados</th>
                        <th class="py-2 text-right">Abiertos</th>
                        <th class="py-2 text-right">Espera prom. (mm:ss)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porServicio as $f)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-bold">{{ $f->servicio }}</td>
                            <td class="py-2 text-right">{{ $f->total }}</td>
                            <td class="py-2 text-right font-black text-guinda">{{ $f->atendidos }}</td>
                            <td class="py-2 text-right">{{ $f->cancelados }}</td>
                            <td class="py-2 text-right">{{ $f->abiertos }}</td>
                            <td class="py-2 text-right">{{ $fmt($f->seg_espera) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-400">Sin turnos en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p class="text-xs text-gray-400 mt-3">
                "Espera" = desde que se generó el turno hasta que se llamó. "Atención" = desde el llamado hasta que se marcó atendido.
            </p>
        </div>

    </div>

</body>
</html>
