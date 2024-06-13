<?php

namespace App\Http\Middleware;

use App\Services\Public\Scorm\ScormUserTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScormUserIdentifier
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $tracking_id = $request->get('tracking_id');
        if (!$tracking_id) {
            return $this->handleMiddlewareVerificationFail($request);
        }

        $scormUserTokenService = $this->getScormServiceInstance();
        if ($scormUserTokenService) {
            /** USER WHO IS PLAYING THE SCORM */
            $scormUser = $scormUserTokenService->getTokenUser($tracking_id);
            if ($scormUser) {
                /** PASSING THE USER IN THE REQUEST */
                $request->merge(['scormUser' => $scormUser]);

                return $next($request);
            }
        }

        return $this->handleMiddlewareVerificationFail($request);
    }

    public function getScormServiceInstance(): ScormUserTokenService|false
    {
        try {
            /* @var ScormUserTokenService $scormService */
            $scormService = app()->make(ScormUserTokenService::class);

            return $scormService;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function handleMiddlewareVerificationFail(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('response.unauthorized')], Response::HTTP_UNAUTHORIZED);
        }
        abort(Response::HTTP_UNAUTHORIZED);
    }
}
