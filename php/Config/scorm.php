<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SCORM FILE BASE PATH
    |--------------------------------------------------------------------------
    |
    | All the scorm files uploaded are store in this path
    |
    */
    'scorm_root_directory' => env('SCORM_ROOT_DIRECTORY', 'uploads/resources/scorm'),

    /*
      |--------------------------------------------------------------------------
      | SCORM FILE SYSTEM DISK
      |--------------------------------------------------------------------------
      | The storage disk used for the scorm files
      |
     */
    'scorm_filesystem_disk' => env('SCORM_FILESYSTEM_DISK', 's3'),

    /*
      |--------------------------------------------------------------------------
      | SCORM TOKEN EXPIRY DURATION HOUR
      |--------------------------------------------------------------------------
      | Temporary token generated for tracking scorm progress - expiry duration in hour
      | For the expiry time in terms of days - create a multiple of 24
      |
      */
    'scorm_token_expiry_duration_hour' => env('SCORM_TOKEN_EXPIRY_DURATION_HOUR', 24),

    /*
      |--------------------------------------------------------------------------
      | SCORM BASE URL
      |--------------------------------------------------------------------------
      | BASE URL FOR SCORM PLAYER
      */
    'scorm_app_base_url' => env('APP_URL', 'http://localhost:8080/'),
];
