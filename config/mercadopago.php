<?php

return [
    // Credenciales de tu cuenta de Mercado Pago (Producción o Test)
    'access_token' => env('MP_ACCESS_TOKEN'),
    'public_key' => env('MP_PUBLIC_KEY'),

    // Secret que Mercado Pago usa para firmar las notificaciones webhook (Panel MP > Webhooks)
    'webhook_secret' => env('MP_WEBHOOK_SECRET'),

    // URLs a las que Mercado Pago redirige tras el pago
    'back_urls' => [
        'success' => env('MP_BACK_URL_SUCCESS', env('APP_URL').'/pagos/exito'),
        'failure' => env('MP_BACK_URL_FAILURE', env('APP_URL').'/pagos/error'),
        'pending' => env('MP_BACK_URL_PENDING', env('APP_URL').'/pagos/pendiente'),
    ],

    // URL que Mercado Pago llama con cada notificación (configurala también en el panel de MP)
    'notification_url' => env('MP_NOTIFICATION_URL', env('APP_URL').'/webhooks/mercadopago'),
];
