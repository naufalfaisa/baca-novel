<?php

return [
    'secret_key'    => env('XENDIT_SECRET_KEY', ''),
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),
    'invoice_url'   => 'https://api.xendit.co/v2/invoices',

    'subscription' => [
        'price'         => (int) env('SUBSCRIPTION_PRICE', 19000),
        'duration_days' => (int) env('SUBSCRIPTION_DURATION_DAYS', 30),
        'description'   => env('SUBSCRIPTION_DESCRIPTION', 'Bacanovel Premium – 30 Hari'),
        'item_name'     => env('SUBSCRIPTION_ITEM_NAME', 'Bacanovel Premium 30 Hari'),
    ],
];
