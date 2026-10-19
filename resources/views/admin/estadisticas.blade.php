@extends('layouts.app')
@section('titulo', 'Estadísticas')

@section('contenido')
<h1>Estadísticas de atención</h1>

<form class="tarjeta" method="GET" action="{{ route('admin.estadisticas') }}">
    <div class="fila-form">
        <div><label for="fecha_inicio">Desde</label><input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ $inicio }}"></div>
        <div><label for="fecha_fin">Hasta</label><input id="fecha_fin" name="fecha_fin" type="date" value="{{ $fin }}"></div>
        <div><button class="btn" type="submit">Consultar</button></div>
    </div>
</form>

<h2>Rendimiento por operador</h2>
<table>
    <thead>
        <tr>
            <th>Operador</th><th>Área</th><th>Ventanilla</th>
            <th class="num">Atendidos</th><th class="num">No se presentaron</th>
            <th class="num">Atención promedio</th><th class="num">Atención máxima</th><th class="num">Espera promedio</th>
        </tr>
    </thead>
    <tbody>
    @forelse($operadores as $f)
        <tr>
            <td>{{ $f->operador }}</td><td>{{ $f->area }}</td><td>{{ $f->ventanilla }}</td>
            <td class="num">{{ $f->atendidos }}</td><td class="num">{{ $f->ausentes }}</td>
            <td class="num">{{ \App\Support\Duracion::formato($f->prom_atencion) }}</td>
            <td class="num">{{ \App\Support\Duracion::formato($f->max_atencion) }}</td>
            <td class="num">{{ \App\Support\Duracion::formato($f->prom_espera) }}</td>
        </tr>
    @empty
        <tr><td colspan="8" class="gris">Sin turnos atendidos en el periodo.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Resumen por área</h2>
<table>
    <thead>
        <tr>
            <th>Área</th><th class="num">Emitidos</th><th class="num">Atendidos</th><th class="num">No se presentaron</th>
            <th class="num">Pendientes</th><th class="num">Espera promedio</th><th class="num">Atención promedio</th>
        </tr>
    </thead>
    <tbody>
    @forelse($areas as $f)
        <tr>
            <td>{{ $f->area }}</td>
            <td class="num">{{ $f->emitidos }}</td><td class="num">{{ $f->atendidos }}</td><td class="num">{{ $f->ausentes }}</td>
            <td class="num">{{ $f->pendientes }}</td>
            <td class="num">{{ \App\Support\Duracion::formato($f->prom_espera) }}</td>
            <td class="num">{{ \App\Support\Duracion::formato($f->prom_atencion) }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="gris">Sin turnos emitidos en el periodo.</td></tr>
    @endforelse
    </tbody>
</table>
<p class="gris">Los tiempos se calculan con las marcas de hora del servidor: espera = emisión → llamada; atención = llamada → fin.</p>
@endsection
