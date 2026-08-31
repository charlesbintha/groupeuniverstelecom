<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Salesforce OAuth2 Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Salesforce API integration using OAuth2
    | client credentials flow.
    |
    */

    'token_url' => env('SALESFORCE_TOKEN_URL', 'https://universtelecom2022.my.salesforce.com/services/oauth2/token'),

    'client_id' => env('SALESFORCE_CLIENT_ID', ''),

    'client_secret' => env('SALESFORCE_CLIENT_SECRET', ''),

    'api_base' => env('SALESFORCE_API_BASE', 'https://universtelecom2022.my.salesforce.com'),

    'api_version' => env('SALESFORCE_API_VERSION', 'v64.0'),

];
