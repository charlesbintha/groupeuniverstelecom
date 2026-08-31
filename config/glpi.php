<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GLPI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for GLPI API integration for maintenance projects.
    | GLPI uses session-based authentication with user and app tokens.
    |
    | Documentation: https://github.com/glpi-project/glpi/blob/master/apirest.md
    |
    */

    'api_url' => env('GLPI_API_URL', 'https://infra.groupe-universtelecom.com/apirest.php'),

    /*
    |--------------------------------------------------------------------------
    | Authentication (Basic Auth)
    |--------------------------------------------------------------------------
    |
    | GLPI username and password for API authentication.
    | These will be encoded in Base64 for Basic Auth.
    |
    */

    'username' => env('GLPI_USERNAME', ''),

    'password' => env('GLPI_PASSWORD', ''),

    'app_token' => env('GLPI_APP_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Set to false to disable SSL certificate verification.
    | Useful for internal servers with self-signed certificates.
    | WARNING: Only disable in development/internal environments.
    |
    */

    'verify_ssl' => env('GLPI_VERIFY_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Default Entity ID
    |--------------------------------------------------------------------------
    |
    | The default GLPI entity ID where projects will be created.
    | You can find entity IDs in GLPI: Setup > Entities
    |
    */

    'default_entity_id' => env('GLPI_DEFAULT_ENTITY_ID', 0),

    /*
    |--------------------------------------------------------------------------
    | Default Project State ID
    |--------------------------------------------------------------------------
    |
    | The default state/status for new projects in GLPI.
    | Common values:
    | 1 = New
    | 2 = In Progress
    | 3 = Closed
    |
    | You can find state IDs in GLPI: Setup > Dropdowns > Project states
    |
    */

    'default_project_state_id' => env('GLPI_DEFAULT_PROJECT_STATE_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Default Project Type ID
    |--------------------------------------------------------------------------
    |
    | The default type for new projects in GLPI.
    | You can find type IDs in GLPI: Setup > Dropdowns > Project types
    |
    */

    'default_project_type_id' => env('GLPI_DEFAULT_PROJECT_TYPE_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Auto-sync to GLPI
    |--------------------------------------------------------------------------
    |
    | Enable automatic synchronization of maintenance projects to GLPI.
    | When enabled, projects with maintenance_glpi=true will be synced
    | automatically after creation/update.
    |
    */

    'auto_sync' => env('GLPI_AUTO_SYNC', true),

];
