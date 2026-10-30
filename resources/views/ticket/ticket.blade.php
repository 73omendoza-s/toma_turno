<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Turno {{ $turno->folio }}</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    html, body { margin: 0; padding: 0; background: #fff; color: #000; }
    body { width: 72mm; margin: 0 auto; padding: 3mm 0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
    .inst { font-size: 11pt; font-weight: 700; }
    .sub { font-size: 9pt; margin-bottom: 3mm; }
    .banda { background: #000; color: #fff; font-size: 13pt; font-weight: 700; letter-spacing: 2px; padding: 2mm 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .folio { font-size: 52pt; font-weight: 700; line-height: 1.1; margin: 3mm 0 1mm; }
    h2 { font-size: 17pt; margin: 0 0 3mm; }
    .datos { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 2mm 0; margin-bottom: 3mm; }
    .fila { display: flex; justify-content: space-between; font-size: 11pt; padding: .6mm 2mm; }
    .fila.neg { font-weight: 700; }
    .ind { font-size: 12pt; font-weight: 700; }
    p { margin: 1mm 0; font-size: 10pt; }
</style>
</head>
<body>
    <div class="inst">{{ \App\Support\Institucional::get('inst_nombre') }}</div>
    <div class="sub">{{ \App\Support\Institucional::get('inst_subtitulo') }}</div>

    <div class="banda">SU TURNO</div>
    <div class="folio">{{ $turno->folio }}</div>
    <h2>{{ $turno->area->nombre }}</h2>

    <div class="datos">
        <div class="fila"><span>Fecha:</span><span>{{ $turno->created_at->format('d/m/Y') }}</span></div>
        <div class="fila"><span>Hora:</span><span>{{ $turno->created_at->format('H:i') }}</span></div>
        <div class="fila neg"><span>Personas antes que usted:</span><span>{{ $adelante }}</span></div>
    </div>

    <p class="ind">Atienda la pantalla de sala</p>
    <p>Se llamará su folio cuando sea su turno.</p>
    <p style="margin-top:3mm">Gracias por su paciencia<br>Conserve este ticket</p>
</body>
</html>
