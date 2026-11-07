<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserLocation;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchingService
{
    /**
     * Get candidate profiles for discovery deck with distance and compatibility score.
     */
    public function getRecommendations(User $user, int $limit = 20): Collection
    {
        $user->loadMissing(['location', 'preferences', 'interests']);
        $userLoc = $user->location;
        $userPref = $user->preferences ?? new UserPreference();

        $userLat = $userLoc ? (float) $userLoc->latitude : (float) Setting::get('default_latitude', 28.6139);
        $userLng = $userLoc ? (float) $userLoc->longitude : (float) Setting::get('default_longitude', 77.2090);

        $maxDistance = $userPref->max_distance_km ?: (int) Setting::get('default_radius', 50);
        $genderPref = $userPref->gender_preference ?: 'all';
        $minAge = $userPref->min_age ?: 18;
        $maxAge = $userPref->max_age ?: 60;
        $showVerifiedOnly = (bool) $userPref->show_verified_only;
        $religionPref = $userPref->preferred_religion;
        $goalPref = $userPref->preferred_relationship_goal;

        // IDs of users already swiped by this user
        $swipedUserIds = $user->swipes()->pluck('target_user_id')->toArray();

        // IDs of users blocked by or who blocked this user
        $blockedByMe = UserBlock::where('user_id', $user->id)->pluck('blocked_user_id')->toArray();
        $blockedMe = UserBlock::where('blocked_user_id', $user->id)->pluck('user_id')->toArray();
        $excludedUserIds = array_unique(array_merge([$user->id], $swipedUserIds, $blockedByMe, $blockedMe));

        // User's own interest IDs for overlap calculation
        $myInterestIds = $user->interests->pluck('id')->toArray();
        $myGoal = $user->profile?->relationship_goal;
        $myReligion = $user->profile?->religion;

        // Query candidates
        $query = User::with(['profile', 'location', 'photos', 'interests'])
            ->where('status', 'active')
            ->whereNotIn('id', $excludedUserIds);

        // Gender filter
        if ($genderPref !== 'all') {
            $query->where('gender', $genderPref);
        }

        // Age filter
        $query->whereBetween('age', [$minAge, $maxAge]);

        // Verified filter
        if ($showVerifiedOnly) {
            $query->where('is_verified', true);
        }

        // Religion & Goal filters if strictly specified in preferences
        if (!empty($religionPref)) {
            $query->whereHas('profile', function (Builder $q) use ($religionPref) {
                $q->where('religion', $religionPref);
            });
        }

        if (!empty($goalPref)) {
            $query->whereHas('profile', function (Builder $q) use ($goalPref) {
                $q->where('relationship_goal', $goalPref);
            });
        }

        // Must have at least one approved photo
        $query->whereHas('photos', function (Builder $q) {
            $q->where('is_approved', true);
        });

        // Fetch pool of candidates
        $candidates = $query->limit(100)->get();

        // Weight configuration from settings
        $wProximity = (int) Setting::get('weight_proximity', 40);
        $wInterests = (int) Setting::get('weight_interests', 30);
        $wGoals = (int) Setting::get('weight_goals', 20);
        $wReligion = (int) Setting::get('weight_religion', 10);
        $totalWeight = max(1, $wProximity + $wInterests + $wGoals + $wReligion);

        $unit = Setting::get('distance_unit', 'km');

        $scoredCandidates = $candidates->map(function (User $candidate) use (
            $userLat,
            $userLng,
            $unit,
            $maxDistance,
            $myInterestIds,
            $myGoal,
            $myReligion,
            $wProximity,
            $wInterests,
            $wGoals,
            $wReligion,
            $totalWeight
        ) {
            $candLoc = $candidate->location;
            $candLat = $candLoc ? (float) $candLoc->latitude : $userLat;
            $candLng = $candLoc ? (float) $candLoc->longitude : $userLng;

            // Distance via Haversine
            $distance = $this->calculateHaversineDistance($userLat, $userLng, $candLat, $candLng, $unit);

            // 1. Proximity score (0 to 100): closer = higher
            $proximityScore = max(0, 100 - min(100, ($distance / max(1, $maxDistance)) * 100));

            // 2. Interest overlap score (0 to 100)
            $candInterestIds = $candidate->interests->pluck('id')->toArray();
            $commonInterests = array_intersect($myInterestIds, $candInterestIds);
            $overlapCount = count($commonInterests);
            $interestScore = !empty($myInterestIds)
                ? min(100, ($overlapCount / max(1, count($myInterestIds))) * 100)
                : 50;

            // 3. Goal compatibility score
            $candGoal = $candidate->profile?->relationship_goal;
            $goalScore = ($myGoal && $candGoal && $myGoal === $candGoal) ? 100 : 40;

            // 4. Religion compatibility score
            $candReligion = $candidate->profile?->religion;
            $religionScore = ($myReligion && $candReligion && $myReligion === $candReligion) ? 100 : 50;

            // Composite Compatibility Score (0 to 100%)
            $compositeScore = round(
                (($proximityScore * $wProximity) +
                 ($interestScore * $wInterests) +
                 ($goalScore * $wGoals) +
                 ($religionScore * $wReligion)) / $totalWeight
            );

            // Format public profile data (strictly hide private matching preferences)
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'age' => $candidate->age,
                'gender' => $candidate->gender,
                'is_verified' => (bool) $candidate->is_verified,
                'is_premium' => (bool) $candidate->is_premium,
                'distance' => $distance,
                'distance_unit' => $unit,
                'compatibility_score' => $compositeScore,
                'location' => [
                    'city' => $candLoc?->city,
                    'state' => $candLoc?->state,
                    'country' => $candLoc?->country,
                ],
                'profile' => [
                    'bio' => $candidate->profile?->bio,
                    'occupation' => $candidate->profile?->occupation,
                    'education' => $candidate->profile?->education,
                    'height' => $candidate->profile?->height,
                    'religion' => $candidate->profile?->religion,
                    'relationship_goal' => $candidate->profile?->relationship_goal,
                    'drinking' => $candidate->profile?->drinking,
                    'smoking' => $candidate->profile?->smoking,
                    'workout' => $candidate->profile?->workout,
                    'zodiac_sign' => $candidate->profile?->zodiac_sign,
                    'hometown' => $candidate->profile?->hometown,
                    'languages' => $candidate->profile?->languages ?? [],
                ],
                'photos' => $candidate->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => $p->url,
                    'is_primary' => (bool) $p->is_primary,
                ]),
                'interests' => $candidate->interests->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'icon' => $i->icon,
                    'category' => $i->category,
                ]),
            ];
        });

        // Filter by max distance and sort by compatibility score desc, then proximity
        return $scoredCandidates
            ->filter(fn ($item) => $item['distance'] <= ($maxDistance * 1.5))
            ->sortByDesc('compatibility_score')
            ->values()
            ->take($limit);
    }

    /**
     * Get nearby profiles within specified radius.
     */
    public function getNearby(User $user, int $radiusKm = 50, int $limit = 20): Collection
    {
        $userLoc = $user->location;
        $userLat = $userLoc ? (float) $userLoc->latitude : (float) Setting::get('default_latitude', 28.6139);
        $userLng = $userLoc ? (float) $userLoc->longitude : (float) Setting::get('default_longitude', 77.2090);
        $unit = Setting::get('distance_unit', 'km');

        // Exclude self and blocked
        $blockedByMe = UserBlock::where('user_id', $user->id)->pluck('blocked_user_id')->toArray();
        $blockedMe = UserBlock::where('blocked_user_id', $user->id)->pluck('user_id')->toArray();
        $excluded = array_unique(array_merge([$user->id], $blockedByMe, $blockedMe));

        $candidates = User::with(['profile', 'location', 'photos'])
            ->where('status', 'active')
            ->whereNotIn('id', $excluded)
            ->whereHas('photos')
            ->limit(100)
            ->get();

        return $candidates->map(function (User $cand) use ($userLat, $userLng, $unit) {
            $candLoc = $cand->location;
            $candLat = $candLoc ? (float) $candLoc->latitude : $userLat;
            $candLng = $candLoc ? (float) $candLoc->longitude : $userLng;
            $dist = $this->calculateHaversineDistance($userLat, $userLng, $candLat, $candLng, $unit);

            return [
                'id' => $cand->id,
                'name' => $cand->name,
                'age' => $cand->age,
                'gender' => $cand->gender,
                'is_verified' => (bool) $cand->is_verified,
                'distance' => $dist,
                'distance_unit' => $unit,
                'city' => $candLoc?->city,
                'avatar' => $cand->photos->where('is_primary', true)->first()?->url ?? $cand->photos->first()?->url,
                'occupation' => $cand->profile?->occupation,
            ];
        })
        ->filter(fn ($item) => $item['distance'] <= $radiusKm)
        ->sortBy('distance')
        ->values()
        ->take($limit);
    }

    /**
     * Standard Haversine distance formula.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2, string $unit = 'km'): float
    {
        $earthRadius = $unit === 'miles' ? 3959.0 : 6371.0;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 1);
    }
}
