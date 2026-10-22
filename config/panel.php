<?php

/*
|--------------------------------------------------------------------------
| Conexión con MiGestión Panel (hub de suscripciones)
|--------------------------------------------------------------------------
| Este sistema funciona como "sistema satélite" del panel: le avisa de cada
| cooperadora nueva, le consulta si está al día y le pide el link de pago.
| El panel, a su vez, avisa por webhook cada cambio de estado.
|
| Los valores se obtienen al dar de alta este sistema en el panel
| (Sistemas → Nuevo): api_key y webhook_secret. Si PANEL_URL o PANEL_API_KEY
| están vacíos, la integración queda apagada y el sistema se administra
| solo con los datos locales de suscripción.
*/

return [

    // Ej: https://panelgestion.migestion.com.ar
    'url' => env('PANEL_URL'),

    // Va en el header X-Api-Key de cada consulta al panel.
    'api_key' => env('PANEL_API_KEY'),

    // Clave con la que el panel firma (HMAC SHA-256) sus webhooks.
    'webhook_secret' => env('PANEL_WEBHOOK_SECRET'),

    'timeout' => (int) env('PANEL_TIMEOUT', 5),

    // Cada cuántos minutos, como máximo, se consulta el estado al panel por
    // cooperadora (el webhook actualiza al instante; esto es la red de
    // seguridad por si un aviso se pierde).
    'cache_minutos' => (int) env('PANEL_CACHE_MINUTOS', 60),

    // Abono que se carga en el panel al registrarse una cooperadora. Si
    // PANEL_MONTO queda vacío, el cliente se registra sin abono y lo cargás
    // a mano desde el panel.
    'plan' => env('PANEL_PLAN', 'Mensual'),
    'monto' => env('PANEL_MONTO'),
    'tipo' => env('PANEL_TIPO', 'recurrente'), // recurrente | unico

];
