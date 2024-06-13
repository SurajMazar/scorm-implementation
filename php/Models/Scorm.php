<?php

namespace App\Models;

use App\Helpers\UtilityHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int     $id
 * @property int     $model_id
 * @property string  $model_type
 * @property string  $uuid
 * @property string  $title
 * @property string  $version
 * @property string  $hash_name
 * @property string  $origin_file
 * @property string  $origin_file_mime
 * @property string  $entry_url
 * @property ?string $scorm_entry_url
 */
class Scorm extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'scorm';

    /**
     * @var string[]
     */
    protected $fillable = [
        'model_id',
        'model_type',
        'uuid',
        'title',
        'version',
        'hash_name',
        'origin_file',
        'origin_file_mime',
        'entry_url',
    ];

    /**
     * @return HasMany
     */
    public function scos(): HasMany
    {
        return $this->hasMany(ScormSco::class, '');
    }

    public function getScormEntryUrlAttribute(): ?string
    {
        if (!$this->entry_url) {
            return null;
        }
        $url = sprintf('%s/%s', $this->uuid, $this->entry_url);

        return sprintf('%s/scorm/%s', UtilityHelper::sanitizeUrl(config('scorm.scorm_app_base_url', '')), $url);
    }
}
