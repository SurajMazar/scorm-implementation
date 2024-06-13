<?php

namespace App\Http\Resources\Public\Scorm;

use App\Models\Scorm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Scorm
 */
class ScormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'version'          => $this->version,
            'hash_name'        => $this->whenHas('hash_name'),
            'origin_file'      => $this->whenHas('origin_file'),
            'origin_file_mime' => $this->whenHas('origin_file_mime'),
            'entry_url'        => $this->whenHas('scorm_entry_url'),
            'scos'             => ScormScoResource::collection($this->whenLoaded('scos')),
        ];
    }
}
