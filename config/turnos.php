<?php

return [
    // true = Laravel imprime directo en la térmica (ESC/POS). false = método anterior (iframe del navegador).
    'impresion_directa' => (bool) env('IMPRESION_DIRECTA', false),

    // windows = impresora compartida de Windows | network = térmica con tarjeta de red (puerto 9100)
    'impresora_tipo' => env('IMPRESORA_TIPO', 'windows'),

    // windows: nombre del recurso compartido (ej. "TM-T88V") o "smb://PC-RECEPCION/TM-T88V" si el servidor es otra PC
    // network: IP de la impresora (ej. "192.168.1.50")
    'impresora_destino' => env('IMPRESORA_DESTINO', 'TM-T88V'),
];
