<?php

return [
    // Slugs con los que diste de alta cada sistema en el panel (Sistemas SaaS)
    'slug_comercial' => env('HUB_SLUG_COMERCIAL', 'gestioncomercial'),
    'slug_contable'  => env('HUB_SLUG_CONTABLE', 'contabilidad'),

    // Endpoints de cada sistema que reciben las órdenes del hub (firmadas con su webhook_secret)
    'ruta_contable'  => '/api/hub/integracion',
    'ruta_comercial' => '/webhooks/hub/integracion-contable',
];
