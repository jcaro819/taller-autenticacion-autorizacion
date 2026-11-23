<?php

return [
    /*
    | Monto global recaudado que reporta GET /api/admin/metricas.
    | El modelo de datos del taller no incluye ventas/inscripciones, así que el
    | valor se toma de la configuración (como en el ejemplo de clase).
    */
    'monto_recaudado' => (int) env('EVENTHUB_MONTO_RECAUDADO', 12500000),
    'moneda' => env('EVENTHUB_MONEDA', 'COP'),
];
