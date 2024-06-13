<?php

namespace App\Services\Public\Scorm;

use App\Helpers\UtilityHelper;
use App\Models\Scorm;
use App\Models\User;
use App\Services\Manage\Scorm\ScormScoService;
use App\Services\Manage\Scorm\Utils\ScormArchiver;

class ScormService
{
    /**
     * @param ScormArchiver         $scormArchiver
     * @param ScormScoService       $scormScoService
     * @param ScormUserTokenService $scormUserTokenService
     */
    public function __construct(
        protected ScormArchiver $scormArchiver,
        protected ScormScoService $scormScoService,
        protected ScormUserTokenService $scormUserTokenService
    ) {
    }

    /**
     * @param string $uuid
     * @param User   $scormUser
     *
     * @return Scorm|false|null
     */
    public function getScorm(string $uuid, User $scormUser): null|Scorm|false
    {
        try {
            /** @var Scorm $scorm */
            $scorm = Scorm::query()
                ->where(['uuid' => $uuid])
                ->with(['scos' => function ($query) use ($scormUser) {
                    $query->with(['scorm', 'children.scoTracking' => function ($query) use ($scormUser) {
                        $query->where('user_id', '=', $scormUser->id);
                    }, 'scoTracking' => function ($query) use ($scormUser) {
                        $query->where('user_id', '=', $scormUser->id);
                    }])->where('sco_parent_id', '=', null);
                }])->firstOrFail();

            return $scorm;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $url
     *
     * @return array|false
     */
    public function generateScormProxy(string $url): false|array
    {
        try {
            /**
             * ADDING THE SCORM FILE DIRECTOR.
             */
            $url = sprintf('%s/%s', $this->scormArchiver->scormRootDirectory, $url);
            if (!$this->checkExtension($url, ['js']) && !$this->checkExtension($url, ['json']) && str_contains($url, 'html') === false) {
                logger()->info($this->scormArchiver->storage->url($url));

                return [
                    'url' => $this->scormArchiver->storage->url($url),
                ];
            }
            $contentType = 'text/html';
            if ($this->checkExtension($url, ['js'])) {
                $contentType = 'text/javascript';
            } elseif ($this->checkExtension($url, ['json'])) {
                $contentType = 'application/json';
            } elseif ($this->checkExtension($url, ['css'])) {
                $contentType = 'text/css';
            }

            return [
                'binary'       => $this->scormArchiver->storage->get($url),
                'content_type' => $contentType,
            ];
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param       $url
     * @param array $allowed
     *
     * @return bool
     */
    public function checkExtension($url, array $allowed = []): bool
    {
        try {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['path'])) {
                $path = pathinfo($parsedUrl['path'], PATHINFO_BASENAME);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return in_array($extension, $allowed);
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param Scorm $scorm
     *
     * @return false|string
     */
    public function generateScormPlayerUrl(Scorm $scorm): false|string
    {
        try {
            /** @var User $authUser */
            $authUser = auth()->user();
            $scormUserToken = $this->scormUserTokenService->getUserScormToken($authUser);
            if (!$scormUserToken) {
                return false;
            }

            return sprintf(
                '%s/scorm-player/%s?tracking_id=%s&language=%s',
                UtilityHelper::sanitizeUrl(config('scorm.scorm_app_base_url', '')),
                $scorm->uuid,
                $scormUserToken->token,
                app()->getLocale()
            );
        } catch (\Exception $exception) {
            return false;
        }
    }
}
