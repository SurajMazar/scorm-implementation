<?php

namespace App\Services\Manage\Scorm\Tracking;

use Illuminate\Support\Collection;

class ScormTrackingBaseSerializer
{
    /**
     * @var string[]
     */
    protected array $FIELDS = [];

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getField(string $key): ?string
    {
        if (!array_key_exists($key, $this->FIELDS)) {
            return null;
        }

        return $this->FIELDS[$key];
    }

    /**
     * @param array|Collection|null $track
     *
     * @return array
     */
    public function getCmiData(array|Collection|null $track = null): array
    {
        $cmi = [];

        if (!$track || (count($track) === 0)) {
            return $cmi;
        }

        foreach ($this->FIELDS as $key => $value) {
            $cmi[$key] = data_get($track, $value);
        }

        return $cmi;
    }

    public function getTrackingData(array $data): array
    {
        $trackingData = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->FIELDS)) {
                $trackingData[$this->FIELDS[$key]] = $value;
            }
        }

        return $trackingData;
    }
}
