<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $user_id
 * @property string $token
 * @property string $expires_at
 * @property User   $user
 */
class ScormUserToken extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'scorm_user_token';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (ScormUserToken $scormUserToken) {
            $scormUserToken->expires_at = Carbon::now()->addHours(config('scorm.scorm_token_expiry_duration_hour', 24));

            return $scormUserToken;
        });
    }
}
