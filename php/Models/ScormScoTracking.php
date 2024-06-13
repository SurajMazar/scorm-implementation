<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScormScoTracking extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'scorm_sco_tracking';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'sco_id',
        'uuid',
        'progression',
        'score_raw',
        'score_min',
        'score_max',
        'score_scaled',
        'lesson_status',
        'completion_status',
        'session_time',
        'total_time_int',
        'total_time_string',
        'entry',
        'suspend_data',
        'credit',
        'exit_mode',
        'lesson_location',
        'lesson_mode',
        'is_locked',
        'details',
        'latest_date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ScormScoTracking $scormScoTracking) {
            $scormScoTracking->uuid = Str::uuid();

            return $scormScoTracking;
        });
    }
}
