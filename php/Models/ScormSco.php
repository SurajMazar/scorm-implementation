<?php

namespace App\Models;

use App\Helpers\UtilityHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int               $id
 * @property string            $uuid
 * @property int               $scorm_id
 * @property int               $sco_parent_id
 * @property string            $entry_url
 * @property string            $identifier
 * @property string            $title
 * @property int               $visible
 * @property string            $sco_parameters
 * @property string            $launch_data
 * @property string            $max_time_allowed
 * @property string            $time_limit_action
 * @property string            $block
 * @property int               $score_int
 * @property float             $score_decimal
 * @property float             $completion_threshold
 * @property string            $prerequisites
 * @property ?string           $scorm_entry_url
 * @property Scorm             $scorm
 * @property ?ScormScoTracking $scoTracking
 */
class ScormSco extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'scorm_sco';

    /**
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'scorm_id',
        'sco_parent_id',
        'entry_url',
        'identifier',
        'title',
        'visible',
        'sco_parameters',
        'launch_data',
        'max_time_allowed',
        'time_limit_action',
        'block',
        'score_int',
        'score_decimal',
        'completion_threshold',
        'prerequisites',
    ];

    /**
     * @return BelongsTo
     */
    public function scorm(): BelongsTo
    {
        return $this->belongsTo(Scorm::class, 'scorm_id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'sco_parent_id', 'id');
    }

    /**
     * @return string|null
     */
    public function getScormEntryUrlAttribute(): ?string
    {
        if (!$this->entry_url) {
            return null;
        }
        $url = sprintf('%s/%s', data_get($this->scorm, 'uuid'), $this->entry_url);

        return sprintf('%s/scorm/%s', UtilityHelper::sanitizeUrl(config('scorm.scorm_app_base_url', '')), $url);
    }

    /**
     * @return HasOne
     */
    public function scoTracking(): HasOne
    {
        return $this->hasOne(ScormScoTracking::class, 'sco_id');
    }
}
