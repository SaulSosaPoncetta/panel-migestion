<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alta de cooperadoras
    |--------------------------------------------------------------------------
    | Si está en true, cualquier institución puede registrarse desde /register
    | y arranca con un período de prueba. Ponelo en false para que las altas
    | se hagan únicamente desde tu panel de gestión.
    */
    'registro_abierto' => (bool) env('SAAS_REGISTRO_ABIERTO', true),

    /*
    |--------------------------------------------------------------------------
    | Período de prueba y tolerancia
    |--------------------------------------------------------------------------
    | dias_prueba: duración de la prueba gratuita al registrarse.
    | dias_gracia: días extra de acceso luego de vencido el abono, antes de
    |              bloquear la cuenta.
    */
    'dias_prueba' => (int) env('SAAS_DIAS_PRUEBA', 30),
    'dias_gracia' => (int) env('SAAS_DIAS_GRACIA', 7),

    /*
    |--------------------------------------------------------------------------
    | Contacto / pago
    |--------------------------------------------------------------------------
    | Se muestra en la pantalla de cuenta vencida o suspendida.
    */
    'url_pago' => env('SAAS_URL_PAGO', 'https://www.migestion.com.ar'),
    'email_contacto' => env('SAAS_EMAIL_CONTACTO'),

];
