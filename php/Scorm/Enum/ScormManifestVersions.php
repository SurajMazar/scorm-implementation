<?php

namespace App\Services\Manage\Scorm\Enum;

enum ScormManifestVersions: string
{
    // 1.2 VERSION
    case SCORM_12 = '1.2';

    // 2004 VERSIONS
    case CAM_1_3 = 'CAM 1.3';
    case SCORM_2004_3RD_EDITION = '2004 3rd Edition';
    case SCORM_2004_4TH_EDITION = '2004 4th Edition';
}
