<?php

return [
    'url' => env('API_EMMPLOYEE_URL', env('API_EMPLOYEE_URL', '')),
    'header_name' => env('API_EMPLOYEE', 'X-API-KEY'),
    'header_value' => env('API_EMPLOYEE_KEY', ''),
    'timeout' => env('API_EMPLOYEE_TIMEOUT', 15),
    'verify_ssl' => env('API_EMPLOYEE_VERIFY_SSL', true),
];
