<?php

namespace App\Services\Manage\Scorm\Tracking;

class Scorm12Serializer extends ScormTrackingBaseSerializer
{
    /**
     * @var string[]
     */
    protected array $FIELDS = [
        'cmi.progress_measure'     => 'progression',
        'cmi.core.score.raw'       => 'score_raw',
        'cmi.core.score.min'       => 'score_min',
        'cmi.core.score.max'       => 'score_max',
        'cmi.core.lesson_status'   => 'lesson_status',
        'cmi.core.session_time'    => 'session_time',
        'cmi.core.total_time'      => 'total_time_int',
        'cmi.core.entry'           => 'entry',
        'cmi.suspend_data'         => 'suspend_data',
        'cmi.core.credit'          => 'credit',
        'cmi.core.exit'            => 'exit_mode',
        'cmi.core.lesson_location' => 'lesson_location',
        'cmi.core.lesson_mode'     => 'lesson_mode',
    ];
}
