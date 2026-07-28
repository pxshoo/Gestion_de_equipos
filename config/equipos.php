<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Correos de notificación de inventario
    |--------------------------------------------------------------------------
    |
    | Direcciones que recibirán un correo automático cada vez que se cree,
    | edite o reasigne un equipo. Se definen en el .env separadas por coma.
    |
    */

    'notification_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('EQUIPO_NOTIFICATION_EMAILS', ''))
    ))),

];
