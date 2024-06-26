<?php

namespace App\Services\Manage\Scorm\Tracking;

class Scorm2004Serializer extends ScormTrackingBaseSerializer
{
    /**
     * @var string[]
     */
    protected array $FIELDS = [
        'cmi.progress_measure'  => 'progression',
        'cmi.score.raw'         => 'score_raw',
        'cmi.score.min'         => 'score_min',
        'cmi.score.max'         => 'score_max',
        'cmi.score.scaled'      => 'score_scaled',
        'cmi.success_status'    => 'lesson_status',
        'cmi.completion_status' => 'completion_status',
        'cmi.session_time'      => 'session_time',
        'cmi.total_time'        => 'total_time_string',
        'cmi.entry'             => 'entry',
        'cmi.suspend_data'      => 'suspend_data',
        'cmi.credit'            => 'credit',
        'cmi.exit'              => 'exit_mode',
        'cmi.location'          => 'lesson_location',
        'cmi.mode'              => 'lesson_mode',
    ];
}
