<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Microsoft Graph API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Microsoft Graph API integration (MS Planner)
    | using OAuth2 client credentials flow with Azure AD.
    |
    */

    'tenant_id' => env('MS_GRAPH_TENANT_ID', ''),

    'client_id' => env('MS_GRAPH_CLIENT_ID', ''),

    'client_secret' => env('MS_GRAPH_CLIENT_SECRET', ''),

    'scope' => env('MS_GRAPH_SCOPE', 'https://graph.microsoft.com/.default'),

    /*
    |--------------------------------------------------------------------------
    | Default Microsoft 365 Group
    |--------------------------------------------------------------------------
    |
    | Default M365 Group ID used as owner for created Planner plans.
    | Can be overridden when calling sync methods.
    |
    */

    'default_group_id' => env('MS_GRAPH_DEFAULT_GROUP_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Auto-sync to Planner
    |--------------------------------------------------------------------------
    |
    | Enable automatic synchronization of new projects to Microsoft Planner.
    | When enabled, projects will be synced automatically after creation.
    |
    */

    'auto_sync' => env('MS_GRAPH_AUTO_SYNC', false),

    /*
    |--------------------------------------------------------------------------
    | Allowed Internal Domains
    |--------------------------------------------------------------------------
    |
    | List of allowed email domains for internal users.
    | Users with emails from these domains will be added to M365 groups.
    | Guests and external users (other domains) will be skipped.
    |
    */

    'allowed_domains' => [
        'groupe-universtelecom.com',
        'universtelecom.net',
        'univers-telecom.com',
        'uta.sn',
        'cp-experts.sn',
        'univers-capital.sn',
        'ute.sn',
    ],

];
