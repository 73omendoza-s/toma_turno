<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($cajero) ? 'Editar Cajero' : 'Nuevo Cajero' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-guinda { background: linear-gradient(135deg, #6B0F2B, #8B1A3A); }
        .text-guinda { color: #6B0F2B; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- Header --}}
    <div class="bg-guinda px-8 py-4 flex justify-between items-center shadow-lg">
        <div>
            <h1 class="text-xl font-black uppercase tracking-wider text-white">
                {{ isset($cajero) ? 'Editar Cajero' : 'Nuevo Cajero' }}
            </h1>
            <p class="text-white text-sm">Tesorería General Municipal</p>
        </div>
        <a href="{{ route('supervisor.index') }}"
           class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-lg transition duration-200">
            ← Regresar
        </a>
    </div>

    <div class="p-6 max-w-lg mx-auto">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow p-6">

            <form action="{{ isset($cajero) ? route('supervisor.cajero.update', $cajero->id) : route('supervisor.cajero.store') }}"
                  method="POST">
                @csrf
                @if(isset($cajero))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1">Nombre completo</label>
                        <input type="text" name="nombre" required maxlength="100"
                            value="{{ old('nombre', $cajero->nombre ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                            placeholder="Nombre del empleado"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase()">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1">Clave Empleado</label>
                        <input type="number" name="clave_empleado" required
                               value="{{ old('clave_empleado', $cajero->clave_empleado ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                               placeholder="Ej: 4900">
                    </div>

                    {{-- La ventanilla define el puesto físico (numero_caja = nombre de la ventanilla) --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1">Ventanilla Asignada</label>
                        @if($ventanillas->isEmpty())
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg px-4 py-3 text-sm mb-2">
                                No hay ventanillas creadas. Primero crea una ventanilla en el panel del supervisor.
                            </div>
                        @else
                            <select name="ventanilla_id" required
                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900">
                                <option value="">-- Selecciona una ventanilla --</option>
                                @foreach($ventanillas->groupBy('servicio') as $servicio => $grupo)
                                    <optgroup label="{{ $servicio }}">
                                        @foreach($grupo as $ventanilla)
                                            <option value="{{ $ventanilla->id }}"
                                                {{ old('ventanilla_id', $cajero->ventanilla_id ?? '') == $ventanilla->id ? 'selected' : '' }}>
                                                {{ $ventanilla->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">Usuario</label>
                        <input type="text" name="usuario" required maxlength="50" autocomplete="off"
                               value="{{ old('usuario', $cajero->usuario ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                               placeholder="Usuario de acceso">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Contraseña {{ isset($cajero) ? '(vacío = sin cambios)' : '' }}
                        </label>
                        <input type="password" name="password" autocomplete="new-password"
                               {{ isset($cajero) ? '' : 'required' }}
                               class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-900"
                               placeholder="Mínimo 6 caracteres">
                    </div>

                    @if(isset($cajero))
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="activo" id="activo" value="1"
                               {{ $cajero->activo ? 'checked' : '' }}
                               class="w-4 h-4 accent-red-900">
                        <label for="activo" class="text-sm font-bold text-gray-600">Cajero activo</label>
                    </div>
                    @endif

                </div>

                <button type="submit"
                    class="bg-guinda w-full text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                    {{ isset($cajero) ? 'Guardar Cambios' : 'Crear Cajero' }}
                </button>

            </form>

        </div>

    </div>

</body>
</html>
