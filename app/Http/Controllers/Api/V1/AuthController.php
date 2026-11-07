<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Profile;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserPreference;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Register a new user account.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:male,female,non_binary,other',
            'dob' => 'required|date|before:-18 years',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        if (empty($request->email) && empty($request->phone)) {
            return $this->sendError('Either email or phone number is required.', null, 422);
        }

        return DB::transaction(function () use ($request) {
            $dob = Carbon::parse($request->dob);
            $age = $dob->age;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'dob' => $dob->toDateString(),
                'age' => $age,
                'status' => 'active',
                'is_verified' => false,
                'is_premium' => false,
                'is_demo' => false,
                'last_active_at' => Carbon::now(),
            ]);

            // Create Profile
            Profile::create([
                'user_id' => $user->id,
                'bio' => $request->bio ?? '',
                'occupation' => $request->occupation,
                'education' => $request->education,
            ]);

            // Create Location
            $defaultLat = (float) Setting::get('default_latitude', 28.6139);
            $defaultLng = (float) Setting::get('default_longitude', 77.2090);
            $defaultCity = Setting::get('default_city', 'New Delhi');

            UserLocation::create([
                'user_id' => $user->id,
                'latitude' => $request->latitude ?? $defaultLat,
                'longitude' => $request->longitude ?? $defaultLng,
                'city' => $request->city ?? $defaultCity,
                'state' => $request->state,
                'country' => 'India',
            ]);

            // Create Default Preferences
            $prefGender = match ($user->gender) {
                'male' => 'female',
                'female' => 'male',
                default => 'all',
            };

            UserPreference::create([
                'user_id' => $user->id,
                'gender_preference' => $prefGender,
                'min_age' => max(18, $age - 5),
                'max_age' => min(60, $age + 6),
                'max_distance_km' => (int) Setting::get('default_radius', 50),
            ]);

            // Initialize star wallet
            $user->getOrCreateWallet();

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'age' => $user->age,
                    'is_verified' => (bool) $user->is_verified,
                    'is_premium' => (bool) $user->is_premium,
                ],
            ], 'User registered successfully.', 201);
        });
    }

    /**
     * User Login with email/phone and password.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $login = $request->login;
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid login credentials.', null, 401);
        }

        if (in_array($user->status, ['suspended', 'banned'])) {
            return $this->sendError("Your account has been {$user->status}. Please contact support.", null, 403);
        }

        if ($user->status === 'deactivated') {
            $user->update(['status' => 'active']);
        }

        $user->update(['last_active_at' => Carbon::now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'age' => $user->age,
                'is_verified' => (bool) $user->is_verified,
                'is_premium' => $user->hasActivePremium(),
            ],
        ], 'Login successful.');
    }

    /**
     * Send OTP for phone or email verification / login.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // phone or email
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $result = $this->otpService->sendOtp($request->identifier);

        return $this->sendResponse($result, 'OTP sent successfully.');
    }

    /**
     * Verify OTP and Login / Register.
     */
    public function verifyOtpLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'code' => 'required|string|min:4|max:10',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $isValid = $this->otpService->verifyOtp($request->identifier, $request->code);
        if (!$isValid) {
            return $this->sendError('Invalid or expired OTP code.', null, 400);
        }

        $identifier = $request->identifier;
        $user = User::where('phone', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if ($user) {
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $user->email_verified_at = Carbon::now();
            } else {
                $user->phone_verified_at = Carbon::now();
            }
            $user->last_active_at = Carbon::now();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'is_new_user' => false,
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_verified' => (bool) $user->is_verified,
                    'is_premium' => $user->hasActivePremium(),
                ],
            ], 'Login successful via OTP.');
        }

        return $this->sendResponse([
            'is_new_user' => true,
            'identifier' => $identifier,
            'message' => 'OTP verified. Please proceed to complete profile registration.',
        ], 'OTP verified.');
    }

    /**
     * Request Password Reset OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return $this->sendError('No account found with this identifier.', null, 404);
        }

        $result = $this->otpService->sendOtp($request->identifier, 'password reset');

        return $this->sendResponse($result, 'Password reset OTP sent.');
    }

    /**
     * Reset Password using OTP.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'code' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $isValid = $this->otpService->verifyOtp($request->identifier, $request->code);
        if (!$isValid) {
            return $this->sendError('Invalid or expired OTP code.', null, 400);
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return $this->sendError('User not found.', null, 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->sendResponse(null, 'Password has been reset successfully. Please log in with your new password.');
    }

    /**
     * Logout current device session.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logged out successfully.');
    }
}
