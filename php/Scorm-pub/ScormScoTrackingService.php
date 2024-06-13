<?php

namespace App\Services\Public\Scorm;

use App\Models\ScormSco;
use App\Models\ScormScoTracking;
use App\Services\Manage\Scorm\Enum\ScormVersions;
use App\Services\Manage\Scorm\Tracking\Scorm12Serializer;
use App\Services\Manage\Scorm\Tracking\Scorm2004Serializer;

class ScormScoTrackingService
{
    /**
     * @param int    $userId
     * @param string $scoUUID
     * @param string $version
     * @param array  $data
     *
     * @return false|ScormScoTracking
     */
    public function store(int $userId, string $scoUUID, string $version, array $data): false|ScormScoTracking
    {
        try {
            $preparedData = $this->prepareTrackingData($version, $data);
            $scoId = $this->getScoIdViaUUID($scoUUID);

            if ($scoId === false || $preparedData === false) {
                return false;
            }

            /** @var ScormScoTracking $scoTracking */
            $scoTracking = ScormScoTracking::query()->updateOrCreate([
                'user_id' => $userId,
                'sco_id'  => $scoId,
            ], $preparedData);

            return $scoTracking;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $version
     * @param        $data
     *
     * @return array|false
     */
    public function prepareTrackingData(string $version, $data): array|false
    {
        try {
            return match ($version) {
                ScormVersions::SCORM_2004->value => (new Scorm2004Serializer())->getTrackingData($data),
                ScormVersions::SCORM_12->value   => (new Scorm12Serializer())->getTrackingData($data),
                default                          => [],
            };
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function getScoIdViaUUID(string $uuid)
    {
        try {
            $sco = ScormSco::query()->where('uuid', '=', $uuid)->firstOrFail();

            return $sco->id;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
