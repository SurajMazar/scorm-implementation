<?php

namespace App\Services\Manage\Scorm\Enum;

enum ScormConstant: string
{
    case MANIFEST_FILE_NAME = 'imsmanifest.xml';

    case SCHEMA_VERSION_TAG = 'schemaversion';
}
