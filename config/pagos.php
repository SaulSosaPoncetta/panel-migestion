<?php

return [
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
