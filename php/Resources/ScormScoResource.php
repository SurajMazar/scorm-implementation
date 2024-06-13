<?php

namespace App\Http\Resources\Public\Scorm;

use App\Models\ScormSco;
use App\Services\Manage\Scorm\Enum\ScormVersions;
use App\Services\Manage\Scorm\Tracking\Scorm12Serializer;
use App\Services\Manage\Scorm\Tracking\Scorm2004Serializer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ScormSco
 */
class ScormScoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $scormVersion = $this->scorm?->version;

        return [
            'uuid'                 => $this->uuid,
            'scorm_id'             => $this->scorm_id,
            'sco_parent_id'        => $this->sco_parent_id,
            'entry_url'            => $this->scorm_entry_url,
            'identifier'           => $this->identifier,
            'title'                => $this->title,
            'visible'              => $this->visible,
            'sco_parameters'       => $this->sco_parameters,
            'launch_data'          => $this->launch_data,
            'max_time_allowed'     => $this->max_time_allowed,
            'time_limit_action'    => $this->time_limit_action,
            'block'                => $this->block,
            'score_int'            => $this->score_int,
            'score_decimal'        => $this->score_decimal,
            'completion_threshold' => $this->completion_threshold,
            'prerequisites'        => $this->prerequisites,
            'tracking'             => $this->scoTracking ? $this->getScormTracking($scormVersion) : null,
            'children'             => self::collection($this->whenLoaded('children')),
        ];
    }

    /**
     * SERIALIZE THE PROGRESS TRACKING AS PER THE SCORM PLAYER AND VERSIONS.
     *
     * @param string $version
     *
     * @return array|null
     */
    public function getScormTracking(string $version): ?array
    {
        return match ($version) {
            ScormVersions::SCORM_2004->value => (new Scorm2004Serializer())->getCmiData($this->scoTracking->toArray() ?: []),
            ScormVersions::SCORM_12->value   => (new Scorm12Serializer())->getCmiData($this->scoTracking->toArray() ?: []),
            default                          => null
        };
    }
}
