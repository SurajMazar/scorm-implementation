<?php

namespace App\Services\Manage\Scorm;

use App\Models\Scorm;
use App\Models\ScormSco;

class ScormScoService
{
    /**
     * @param Scorm    $scorm
     * @param array    $scos
     * @param int|null $parent_id
     *
     * @return ScormSco|false
     */
    public function bulkStore(Scorm $scorm, array $scos, ?int $parent_id = null): ScormSco|false
    {
        try {
            /** @var ScormSco $sco */
            foreach ($scos as $data) {
                $sco = new ScormSco();
                $sco->scorm_id = data_get($scorm, 'id');
                $sco->uuid = data_get($data, 'uuid');
                $sco->sco_parent_id = $parent_id;
                $sco->entry_url = data_get($data, 'entryUrl');
                $sco->identifier = data_get($data, 'identifier');
                $sco->title = data_get($data, 'title');
                $sco->visible = data_get($data, 'visible');
                $sco->sco_parameters = data_get($data, 'parameters');
                $sco->launch_data = data_get($data, 'launchData');
                $sco->max_time_allowed = data_get($data, 'maxTimeAllowed');
                $sco->time_limit_action = data_get($data, 'timeLimitAction');
                $sco->block = data_get($data, 'block');
                $sco->score_int = data_get($data, 'scoreToPassInt');
                $sco->score_decimal = data_get($data, 'scoreToPassDecimal');
                $sco->completion_threshold = data_get($data, 'completionThreshold');
                $sco->prerequisites = data_get($data, 'prerequisites');
                $sco->save();

                $children = data_get($data, 'scoChildren', []);
                if (!empty($children)) {
                    $this->bulkStore($scorm, $children, $sco->id);
                }
            }
        } catch (\Exception $exception) {
            return false;
        }

        return $sco;
    }
}
