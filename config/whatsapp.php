<?php

return [
    'app_id' => env('WHATSAPP_APP_ID', null),
    'app_secret' => env('WHATSAPP_APP_SECRET', null),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', null),
    'business_phone_id' => env('WHATSAPP_BUSINESS_PHONE_ID', null),
    'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    'base_url' => 'https://graph.facebook.com/',
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', null),
];