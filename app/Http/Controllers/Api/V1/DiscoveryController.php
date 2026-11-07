<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryController extends BaseApiController
{
    protected MatchingService $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Get discovery card recommendations ranked by proximity and compatibility.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(50, max(1, (int) $request->input('limit', 20)));

        $recommendations = $this->matchingService->getRecommendations($user, $limit);

        return $this->sendResponse([
            'count' => $recommendations->count(),
            'profiles' => $recommendations,
        ], 'Recommended profiles retrieved.');
    }

    /**
     * Get nearby profiles within radius.
     */
    public function getNearby(Request $request): JsonResponse
    {
        $user = $request->user();
        $radius = (int) $request->input('radius_km', 50);
        $limit = min(50, max(1, (int) $request->input('limit', 20)));

        $nearby = $this->matchingService->getNearby($user, $radius, $limit);

        return $this->sendResponse([
            'radius_km' => $radius,
            'count' => $nearby->count(),
            'profiles' => $nearby,
        ], 'Nearby profiles retrieved.');
    }
}
