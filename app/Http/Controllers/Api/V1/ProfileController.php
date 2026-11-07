<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Interest;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\UserLocation;
use App\Models\UserPreference;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseApiController
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Get authenticated user's complete profile with all relations and balances.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing([
            'profile',
            'location',
            'preferences',
            'photos',
            'interests',
            'starWallet',
            'activeSubscription.plan',
        ]);

        $wallet = $user->getOrCreateWallet();
        $activeSub = $user->activeSubscription;

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'dob' => $user->dob?->toDateString(),
            'age' => $user->age,
            'status' => $user->status,
            'is_verified' => (bool) $user->is_verified,
            'is_premium' => $user->hasActivePremium(),
            'premium_expires_at' => $activeSub ? $activeSub->expires_at->toIso8601String() : null,
            'active_subscription' => $activeSub ? [
                'plan_name' => $activeSub->plan?->name,
                'badge_color' => $activeSub->plan?->badge_color,
                'expires_at' => $activeSub->expires_at->toIso8601String(),
            ] : null,
            'wallet' => [
                'stars_balance' => $wallet->balance,
            ],
            'swipe_limits' => [
                'daily_limit' => $user->getDailySwipeLimit(),
                'used_today' => $user->getSwipesUsedToday(),
                'remaining_today' => $user->getSwipesRemainingToday(),
                'can_swipe' => $user->canSwipeToday(),
            ],
            'profile' => $user->profile,
            'location' => $user->location,
            'preferences' => $user->preferences,
            'photos' => $user->photos,
            'interests' => $user->interests,
        ], 'Profile retrieved successfully.');
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'occupation' => 'nullable|string|max:100',
            'education' => 'nullable|string|max:100',
            'height' => 'nullable|integer|between:120,240',
            'religion' => 'nullable|string|max:50',
            'relationship_goal' => 'nullable|in:long_term,short_term,casual,friendship,marriage',
            'drinking' => 'nullable|in:never,socially,frequently',
            'smoking' => 'nullable|in:never,socially,frequently',
            'workout' => 'nullable|in:never,sometimes,everyday',
            'zodiac_sign' => 'nullable|string|max:30',
            'hometown' => 'nullable|string|max:100',
            'languages' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        if ($request->filled('name')) {
            $user->update(['name' => $request->name]);
        }

        $profile = $user->profile ?: new Profile(['user_id' => $user->id]);
        $profile->fill($request->only([
            'bio',
            'occupation',
            'education',
            'height',
            'religion',
            'relationship_goal',
            'drinking',
            'smoking',
            'workout',
            'zodiac_sign',
            'hometown',
            'languages',
        ]));
        $profile->save();

        return $this->sendResponse($profile, 'Profile updated successfully.');
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $maxPhotos = (int) Setting::get('max_photos_per_user', 6);

        if ($user->photos()->count() >= $maxPhotos) {
            return $this->sendError("You can upload a maximum of {$maxPhotos} photos.", null, 400);
        }

        try {
            $photo = $this->fileService->uploadPhoto(
                $user,
                $request->file('photo'),
                $request->boolean('is_primary', false)
            );

            return $this->sendResponse($photo, 'Photo uploaded successfully.', 201);
        } catch (\Throwable $e) {
            return $this->sendError($e->getMessage(), null, 400);
        }
    }

    /**
     * Delete profile photo.
     */
    public function deletePhoto(Request $request, int $photoId): JsonResponse
    {
        $user = $request->user();
        $photo = Photo::where('id', $photoId)->where('user_id', $user->id)->first();

        if (!$photo) {
            return $this->sendError('Photo not found.', null, 404);
        }

        $this->fileService->deletePhoto($photo);

        return $this->sendResponse(null, 'Photo deleted successfully.');
    }

    /**
     * Set photo as primary.
     */
    public function setPrimaryPhoto(Request $request, int $photoId): JsonResponse
    {
        $user = $request->user();
        $photo = Photo::where('id', $photoId)->where('user_id', $user->id)->first();

        if (!$photo) {
            return $this->sendError('Photo not found.', null, 404);
        }

        $user->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return $this->sendResponse($photo, 'Primary photo updated.');
    }

    /**
     * Sync user interests.
     */
    public function syncInterests(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'interest_ids' => 'required|array|min:1|max:10',
            'interest_ids.*' => 'exists:interests,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $user->interests()->sync($request->interest_ids);

        return $this->sendResponse($user->interests, 'Interests updated successfully.');
    }

    /**
     * List all available interests master data.
     */
    public function listAllInterests(): JsonResponse
    {
        $interests = Interest::all()->groupBy('category');
        return $this->sendResponse($interests, 'Interests retrieved.');
    }

    /**
     * Update user discovery & matching preferences (hidden from public view).
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gender_preference' => 'nullable|in:all,male,female,non_binary',
            'min_age' => 'nullable|integer|between:18,99',
            'max_age' => 'nullable|integer|between:18,99|gte:min_age',
            'max_distance_km' => 'nullable|integer|between:5,500',
            'preferred_religion' => 'nullable|string|max:50',
            'preferred_relationship_goal' => 'nullable|string|max:50',
            'show_verified_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $prefs = $user->preferences ?: new UserPreference(['user_id' => $user->id]);
        $prefs->fill($request->only([
            'gender_preference',
            'min_age',
            'max_age',
            'max_distance_km',
            'preferred_religion',
            'preferred_relationship_goal',
            'show_verified_only',
        ]));
        $prefs->save();

        return $this->sendResponse($prefs, 'Matching preferences updated.');
    }

    /**
     * Update user location.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $location = $user->location ?: new UserLocation(['user_id' => $user->id]);
        $location->fill($request->only([
            'latitude',
            'longitude',
            'city',
            'state',
            'country',
            'address',
        ]));
        $location->save();

        return $this->sendResponse($location, 'Location updated successfully.');
    }

    /**
     * Request profile verification badge.
     */
    public function requestVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'selfie_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $file = $request->file('selfie_photo');
        $uploadDir = public_path('uploads/verifications/' . $user->id);
        if (!\Illuminate\Support\Facades\File::isDirectory($uploadDir)) {
            \Illuminate\Support\Facades\File::makeDirectory($uploadDir, 0755, true, true);
        }

        $filename = 'verification_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);
        $relativePath = 'uploads/verifications/' . $user->id . '/' . $filename;

        $autoVerify = (bool) Setting::get('auto_verify_users', false);

        $user->update([
            'verification_photo' => url($relativePath),
            'verification_submitted_at' => Carbon::now(),
            'is_verified' => $autoVerify,
        ]);

        $message = $autoVerify
            ? 'Profile verified automatically!'
            : 'Verification request submitted. Our admin moderation team will review it shortly.';

        return $this->sendResponse([
            'is_verified' => (bool) $user->is_verified,
            'submitted_at' => $user->verification_submitted_at->toIso8601String(),
        ], $message);
    }

    /**
     * Deactivate account.
     */
    public function deactivateAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['status' => 'deactivated']);
        $user->tokens()->delete();

        return $this->sendResponse(null, 'Account deactivated successfully. You can reactivate anytime by logging in.');
    }

    /**
     * Delete account.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        if (!Setting::get('allow_account_deletion', true)) {
            return $this->sendError('Account deletion is currently disabled by administrator.', null, 403);
        }

        $user = $request->user();
        $user->tokens()->delete();
        $user->delete(); // Soft delete

        return $this->sendResponse(null, 'Account has been deleted successfully.');
    }
}
