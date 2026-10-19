<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $turno->folio }}</title>
    <style>
        /* Rollo de 80 mm: el alto lo define el contenido y la impresora corta al final */
        @page { size: 80mm auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; color: #000; }
        body { width: 72mm; margin: 0 auto; padding: 3mm 0 6mm; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .logo  { display: block; margin: 0 auto 2mm; height: 22mm; width: auto; max-width: 60mm;
                 filter: grayscale(1) contrast(1.6); }   /* la térmica imprime en blanco y negro */
        .inst  { font-size: 13pt; font-weight: 700; text-transform: uppercase; line-height: 1.15; }
        .lema  { font-size: 10pt; font-style: italic; margin-top: 1mm; }
        .linea { border-top: 1px dashed #000; margin: 3mm 0; }
        .area  { font-size: 13pt; font-weight: 700; }
        .etq   { font-size: 11pt; letter-spacing: 2px; margin-top: 2mm; }
        .folio { font-size: 44pt; font-weight: 900; line-height: 1.05; letter-spacing: 1px; margin: 1mm 0 2mm; }
        .dato  { font-size: 10.5pt; margin: 1mm 0; }
        .pie   { font-size: 9.5pt; margin-top: 2mm; }
        @media screen { body { margin-top: 10px; border: 1px solid #ccc; } }
    </style>
</head>
<body>
    <img class="logo" src="{{ \App\Support\Institucional::logo('logo_escudo') }}" alt="" onerror="this.style.display='none'">
    <div class="inst">{{ \App\Support\Institucional::get('inst_nombre') }}</div>
    @if(\App\Support\Institucional::get('inst_lema') !== '')
        <div class="lema">{{ \App\Support\Institucional::get('inst_lema') }}</div>
    @endif

    <div class="linea"></div>

    @if(! empty($direccion))
        <div class="dato" style="font-weight:700">{{ $direccion }}</div>
    @endif
    <div class="area">{{ $turno->area->nombre ?? 'Atención al contribuyente' }}</div>
    <div class="etq">SU TURNO</div>
    <div class="folio">{{ $turno->folio }}</div>

    <div class="linea"></div>

    <div class="dato">{{ $emitido->timezone(config('app.timezone'))->format('d/m/Y  H:i') }}</div>
    <div class="dato">
        @if ($adelante === 0)
            Es el siguiente en ser atendido
        @else
            Turnos antes que usted: <b>{{ $adelante }}</b>
        @endif
    </div>

    <div class="linea"></div>

    <div class="pie">Espere a que su turno aparezca<br>en la pantalla y se anuncie por voz.<br>Conserve este ticket.</div>

    <script>
        // Imprime al abrir. Con Chrome en modo --kiosk-printing sale directo, sin diálogo.
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
