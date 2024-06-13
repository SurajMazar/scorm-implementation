<?php

namespace App\Services\Public\Scorm;

use App\Models\ScormUserToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScormUserTokenService
{
    /**
     * @param User $user
     *
     * @return false|ScormUserToken
     */
    public function getUserScormToken(User $user): false|ScormUserToken
    {
        try {
            /** @var ScormUserToken|null $existing */
            $existing = ScormUserToken::query()->where('user_id', '=', $user->id)->first();

            /** IN CASE OF NO TOKEN */
            if (!$existing) {
                return $this->createToken($user);
            }

            /** IF TOKEN IS EXPIRED THEN NEW TOKEN IS GENERATED */
            if (!$this->checkValid(
                Carbon::now()->addHours(2), // IF THE TOKEN IS GOING TO EXPIRE WITHIN NEXT TWO HOURS REGENERATE TOKEN
                Carbon::parse($existing->expires_at)
            )) {
                $existing->delete(); // DELETES THE OLD TOKEN

                return $this->createToken($user);
            }

            /** REUSE THE OLD TOKEN */
            return $existing;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param User $user
     *
     * @return ScormUserToken|false
     */
    public function createToken(User $user): false|ScormUserToken
    {
        try {
            $generateToken = $this->generateUniqueTokenHash($user->username);

            if (!$generateToken) {
                return false;
            }

            /**
             * @var ScormUserToken $token
             */
            $token = ScormUserToken::query()->create([
                'token'   => $generateToken,
                'user_id' => $user->id,
            ]);

            return $token;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $username
     *
     * @return false|string
     */
    public function generateUniqueTokenHash(string $username): false|string
    {
        try {
            $salt = sprintf('%s-%s-%s', $username, Str::uuid(), time());

            return hash('sha256', $salt);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $token
     *
     * @return User|false
     */
    public function getTokenUser(string $token): false|User
    {
        try {
            /** @var ScormUserToken|null $scormUserToken */
            $scormUserToken = ScormUserToken::query()->where('token', '=', $token)->first();

            if ($scormUserToken && $this->checkValid(Carbon::now(), Carbon::parse($scormUserToken->expires_at))) {
                return $scormUserToken->user;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param Carbon $current
     * @param Carbon $expiry
     *
     * @return bool
     */
    public function checkValid(Carbon $current, Carbon $expiry): bool
    {
        return $current < $expiry;
    }
}
