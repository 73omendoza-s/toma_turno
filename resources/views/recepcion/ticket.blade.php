<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Turno {{ $turno->folio }}</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    html, body { margin: 0; padding: 0; background: #fff; color: #000; }
    body { width: 72mm; margin: 0 auto; padding: 3mm 0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
    .inst { font-family: "Arial Black", Impact, Arial, sans-serif; font-size: 15pt; text-transform: uppercase; line-height: 1.15; }
    .sub { font-family: Arial, Helvetica, sans-serif; font-weight: 400; font-size: 13pt; text-transform: uppercase; margin: 2mm 0 4mm; }
    .su { font-family: Arial, Helvetica, sans-serif; font-weight: 400; font-size: 13pt; margin-bottom: 2mm; }
    .folio { font-family: "Arial Black", Impact, Arial, sans-serif; font-size: 36pt; font-weight: 900; line-height: 1.05; letter-spacing: 1px; margin: 0 0 3mm; }
    h2 { font-family: "Arial Black", Impact, Arial, sans-serif; font-size: 14pt; text-transform: uppercase; margin: 0 0 6mm; }
    .fila { display: flex; justify-content: space-between; font-size: 11pt; padding: .6mm 2mm; }
    .fila.neg { font-weight: 700; }
    .ind { font-size: 12pt; font-weight: 700; margin-top: 6mm; }
    p { margin: 1mm 0; font-size: 10pt; }
</style>
</head>
<body>
    <div class="inst">{{ \App\Support\Institucional::get('inst_nombre') }}</div>
    <div class="sub">{{ \App\Support\Institucional::get('inst_subtitulo') }}</div>

    <div class="su">SU TURNO</div>
    <div class="folio">{{ $turno->folio }}</div>
    <h2>{{ $turno->area->nombre }}</h2>

    <div class="fila"><span>Fecha:</span><span>{{ $turno->created_at->format('d/m/Y') }}</span></div>
    <div class="fila"><span>Hora:</span><span>{{ $turno->created_at->format('H:i') }}</span></div>
    <div class="fila neg"><span>Personas antes que usted:</span><span>{{ $adelante }}</span></div>

    <p class="ind">Atienda la pantalla de sala</p>
    <p>Se llamará su folio cuando sea su turno.</p>
    <p style="margin-top:3mm">Gracias por su paciencia<br>Conserve este ticket</p>
</body>
</html>
