<?php

return [
    // Zona horaria con la que se calculan los vencimientos (la fecha "de hoy" para tus clientes).
    'zona_horaria' => env('PAGOS_ZONA_HORARIA', 'America/Argentina/Buenos_Aires'),

    // Días de aviso antes del vencimiento (se envía un mail en cada uno).
    'avisos_dias' => [7, 3, 1],

    // Días de gracia después del vencimiento antes de pasar la suscripción a "vencida".
    // Los clientes con débito automático tienen además 3 días de margen por demoras del cobro.
    'gracia_dias' => (int) env('PAGOS_GRACIA_DIAS', 0),

    // Mail donde recibís el resumen diario (vencimientos, transferencias por revisar). Vacío = no se envía.
    'admin_email' => env('PAGOS_ADMIN_EMAIL'),

    // Datos que ve el cliente cuando elige pagar por transferencia bancaria.
    // Se cargan en el .env del Panel. Si no hay CBU ni alias, la opción no aparece.
    'transferencia' => [
        'titular' => env('TRANSFER_TITULAR'),
        'banco' => env('TRANSFER_BANCO'),
        'cbu' => env('TRANSFER_CBU'),
        'alias' => env('TRANSFER_ALIAS'),
        'cuit' => env('TRANSFER_CUIT'),
        'activa' => (bool) (env('TRANSFER_CBU') || env('TRANSFER_ALIAS')),
    ],
];
