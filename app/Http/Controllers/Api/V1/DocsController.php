<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class DocsController extends BaseApiController
{
    /**
     * Render interactive web API documentation.
     */
    public function index()
    {
        $baseUrl = url('/api/v1');
        $appName = Setting::get('app_name', 'DilSe Match Dating Platform');
        $endpoints = $this->getEndpointCatalog($baseUrl);

        return view('docs.index', compact('baseUrl', 'appName', 'endpoints'));
    }

    /**
     * Export complete Postman Collection (v2.1).
     */
    public function exportPostman(): JsonResponse
    {
        $baseUrl = url('/api/v1');
        $appName = Setting::get('app_name', 'DilSe Match Dating Platform');
        $catalog = $this->getEndpointCatalog($baseUrl);

        $items = [];
        foreach ($catalog as $groupName => $routes) {
            $groupItems = [];
            foreach ($routes as $route) {
                $groupItems[] = [
                    'name' => $route['title'],
                    'request' => [
                        'method' => $route['method'],
                        'header' => array_map(function ($k, $v) {
                            return ['key' => $k, 'value' => $v, 'type' => 'text'];
                        }, array_keys($route['headers'] ?? []), array_values($route['headers'] ?? [])),
                        'body' => !empty($route['body']) ? [
                            'mode' => 'raw',
                            'raw' => json_encode($route['body'], JSON_PRETTY_PRINT),
                            'options' => ['raw' => ['language' => 'json']],
                        ] : null,
                        'url' => [
                            'raw' => $baseUrl . $route['path'],
                            'protocol' => parse_url($baseUrl, PHP_URL_SCHEME),
                            'host' => explode('.', parse_url($baseUrl, PHP_URL_HOST)),
                            'path' => array_values(array_filter(explode('/', parse_url($baseUrl . $route['path'], PHP_URL_PATH)))),
                        ],
                        'description' => $route['description'],
                    ],
                    'response' => [
                        [
                            'name' => 'Successful Response',
                            'originalRequest' => null,
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'body' => json_encode($route['response_example'], JSON_PRETTY_PRINT),
                        ]
                    ],
                ];
            }
            $items[] = [
                'name' => $groupName,
                'item' => $groupItems,
            ];
        }

        $postmanCollection = [
            'info' => [
                'name' => "{$appName} REST API Collection",
                'description' => "Complete API specifications for {$appName} mobile dating application.",
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $items,
        ];

        return response()->json($postmanCollection)
            ->header('Content-Disposition', 'attachment; filename="dating_app_api_postman_collection.json"');
    }

    /**
     * Complete API endpoints catalog with payloads and responses.
     */
    public function getEndpointCatalog(string $baseUrl): array
    {
        return [
            'Authentication' => [
                [
                    'method' => 'POST',
                    'path' => '/auth/register',
                    'title' => 'Register New User',
                    'auth' => false,
                    'description' => 'Create a new dating profile account with phone/email, dob, gender, and coordinates.',
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'body' => [
                        'name' => 'Aarav Sharma',
                        'email' => 'aarav@example.com',
                        'phone' => '919876543210',
                        'password' => 'Secret@123',
                        'gender' => 'male',
                        'dob' => '1998-05-15',
                        'latitude' => 28.6139,
                        'longitude' => 77.2090,
                        'city' => 'New Delhi',
                        'state' => 'Delhi',
                        'occupation' => 'Software Engineer',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'User registered successfully.',
                        'data' => [
                            'token' => '1|laravel_sanctum_token_string...',
                            'token_type' => 'Bearer',
                            'user' => [
                                'id' => 1,
                                'name' => 'Aarav Sharma',
                                'email' => 'aarav@example.com',
                                'gender' => 'male',
                                'age' => 28,
                                'is_verified' => false,
                                'is_premium' => false,
                            ],
                        ],
                        'code' => 201,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/login',
                    'title' => 'User Login',
                    'auth' => false,
                    'description' => 'Authenticate user via email or phone number and password.',
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'body' => [
                        'login' => 'aarav@example.com',
                        'password' => 'Secret@123',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Login successful.',
                        'data' => [
                            'token' => '2|laravel_sanctum_token_string...',
                            'token_type' => 'Bearer',
                            'user' => [
                                'id' => 1,
                                'name' => 'Aarav Sharma',
                                'email' => 'aarav@example.com',
                                'gender' => 'male',
                                'age' => 28,
                                'is_verified' => true,
                                'is_premium' => true,
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/send-otp',
                    'title' => 'Send OTP',
                    'auth' => false,
                    'description' => 'Send a 6-digit OTP to mobile number or email.',
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => ['identifier' => '919876543210'],
                    'response_example' => [
                        'status' => true,
                        'message' => 'OTP sent successfully.',
                        'data' => [
                            'identifier' => '919876543210',
                            'expires_at' => '2026-11-08T00:30:00Z',
                            'demo_code' => '123456',
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/verify-otp',
                    'title' => 'Verify OTP & Login',
                    'auth' => false,
                    'description' => 'Verify OTP code and authenticate user session.',
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => ['identifier' => '919876543210', 'code' => '123456'],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Login successful via OTP.',
                        'data' => [
                            'is_new_user' => false,
                            'token' => '3|laravel_sanctum_token...',
                            'user' => ['id' => 1, 'name' => 'Aarav Sharma'],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/logout',
                    'title' => 'Logout',
                    'auth' => true,
                    'description' => 'Revoke authenticated session token.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Accept' => 'application/json'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Logged out successfully.',
                        'data' => null,
                        'code' => 200,
                    ],
                ],
            ],

            'Profile Management' => [
                [
                    'method' => 'GET',
                    'path' => '/user/profile',
                    'title' => 'Get Current User Profile',
                    'auth' => true,
                    'description' => 'Fetch authenticated user profile with photos, preferences, star wallet, active subscription, and remaining swipe limits.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Accept' => 'application/json'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Profile retrieved successfully.',
                        'data' => [
                            'id' => 1,
                            'name' => 'Aarav Sharma',
                            'age' => 28,
                            'gender' => 'male',
                            'is_verified' => true,
                            'is_premium' => true,
                            'wallet' => ['stars_balance' => 25],
                            'swipe_limits' => [
                                'daily_limit' => -1,
                                'used_today' => 12,
                                'remaining_today' => -1,
                                'can_swipe' => true,
                            ],
                            'profile' => [
                                'bio' => 'Software engineer, cafe enthusiast, dog lover.',
                                'occupation' => 'Product Engineer',
                                'education' => 'IIT Delhi',
                                'height' => 180,
                                'religion' => 'Hindu',
                                'relationship_goal' => 'long_term',
                            ],
                            'location' => ['city' => 'New Delhi', 'latitude' => 28.6139, 'longitude' => 77.2090],
                            'photos' => [
                                ['id' => 10, 'url' => 'https://example.com/uploads/photos/1/photo_1.jpg', 'is_primary' => true],
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'PUT',
                    'path' => '/user/profile',
                    'title' => 'Update Profile Details',
                    'auth' => true,
                    'description' => 'Update bio, occupation, education, height, religion, habits, and relationship goals.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'bio' => 'Passionate architect & travel blogger. Seeking genuine vibes.',
                        'occupation' => 'Senior Architect',
                        'height' => 178,
                        'religion' => 'Hindu',
                        'relationship_goal' => 'marriage',
                        'drinking' => 'socially',
                        'smoking' => 'never',
                        'workout' => 'everyday',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Profile updated successfully.',
                        'data' => ['bio' => 'Passionate architect & travel blogger...'],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/photos',
                    'title' => 'Upload Profile Photo',
                    'auth' => true,
                    'description' => 'Upload a profile photo (multipart/form-data, max 5MB).',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => ['photo' => '(binary file)', 'is_primary' => 1],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Photo uploaded successfully.',
                        'data' => [
                            'id' => 15,
                            'url' => 'https://example.com/uploads/photos/1/photo_169999999.jpg',
                            'is_primary' => true,
                        ],
                        'code' => 201,
                    ],
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/user/photos/{id}',
                    'title' => 'Delete Profile Photo',
                    'auth' => true,
                    'description' => 'Delete an uploaded photo by its ID.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Photo deleted successfully.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/interests',
                    'title' => 'Sync Interests',
                    'auth' => true,
                    'description' => 'Sync selected interest IDs for the user profile.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => ['interest_ids' => [1, 3, 7, 12]],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Interests updated successfully.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'PUT',
                    'path' => '/user/preferences',
                    'title' => 'Update Matching Preferences',
                    'auth' => true,
                    'description' => 'Update hidden matching preferences (radius, age range, gender).',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'gender_preference' => 'female',
                        'min_age' => 21,
                        'max_age' => 32,
                        'max_distance_km' => 40,
                        'show_verified_only' => false,
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Matching preferences updated.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/location',
                    'title' => 'Update GPS Location',
                    'auth' => true,
                    'description' => 'Update user latitude, longitude, and city.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'latitude' => 19.0760,
                        'longitude' => 72.8777,
                        'city' => 'Mumbai',
                        'state' => 'Maharashtra',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Location updated successfully.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/verify-request',
                    'title' => 'Request Verification Badge',
                    'auth' => true,
                    'description' => 'Upload a selfie/document for blue badge verification.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => ['selfie_photo' => '(binary file)'],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Verification request submitted. Our admin moderation team will review it shortly.',
                        'code' => 200,
                    ],
                ],
            ],

            'Discovery & Matching Engine' => [
                [
                    'method' => 'GET',
                    'path' => '/matches/recommendations',
                    'title' => 'Discovery Card Deck',
                    'auth' => true,
                    'description' => 'Get candidate profiles ranked by distance and compatibility score (0-100%). Private matching preferences are kept hidden.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Recommended profiles retrieved.',
                        'data' => [
                            'count' => 15,
                            'profiles' => [
                                [
                                    'id' => 102,
                                    'name' => 'Ananya Verma',
                                    'age' => 25,
                                    'gender' => 'female',
                                    'is_verified' => true,
                                    'distance' => 6.4,
                                    'distance_unit' => 'km',
                                    'compatibility_score' => 88,
                                    'location' => ['city' => 'New Delhi'],
                                    'profile' => [
                                        'bio' => 'Product designer, loves espresso & indie films.',
                                        'occupation' => 'UX Lead',
                                        'education' => 'NIFT Delhi',
                                        'relationship_goal' => 'long_term',
                                    ],
                                    'photos' => [
                                        ['id' => 201, 'url' => 'https://images.unsplash.com/...', 'is_primary' => true],
                                    ],
                                    'interests' => [
                                        ['id' => 2, 'name' => 'Coffee & Cafes', 'icon' => 'fa-coffee'],
                                        ['id' => 10, 'name' => 'Yoga & Meditation', 'icon' => 'fa-spa'],
                                    ],
                                ],
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/matches/nearby',
                    'title' => 'Nearby Profiles Explorer',
                    'auth' => true,
                    'description' => 'List profiles strictly within specified distance radius (default: 50km).',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Nearby profiles retrieved.',
                        'data' => [
                            'radius_km' => 25,
                            'count' => 10,
                            'profiles' => [
                                ['id' => 88, 'name' => 'Priya Patel', 'age' => 24, 'distance' => 4.2, 'city' => 'New Delhi'],
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
            ],

            'Swipes & Matches' => [
                [
                    'method' => 'POST',
                    'path' => '/swipes',
                    'title' => 'Submit Swipe (Like / Dislike / Star)',
                    'auth' => true,
                    'description' => 'Swipe on candidate profile. Automatically detects mutual like and creates match record.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'target_user_id' => 102,
                        'type' => 'like', // or 'dislike', 'star'
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'It\'s a match!',
                        'data' => [
                            'success' => true,
                            'is_match' => true,
                            'match' => [
                                'id' => 42,
                                'matched_at' => '2026-11-08T00:45:00Z',
                                'partner' => [
                                    'id' => 102,
                                    'name' => 'Ananya Verma',
                                    'age' => 25,
                                    'avatar' => 'https://images.unsplash.com/...',
                                ],
                            ],
                            'remaining_swipes' => 48,
                            'stars_balance' => 5,
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/swipes/limits',
                    'title' => 'Check Swipe Quota',
                    'auth' => true,
                    'description' => 'View daily swipes remaining, reset time, and star balance.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Limits retrieved.',
                        'data' => [
                            'is_premium' => false,
                            'daily_limit' => 50,
                            'used_today' => 14,
                            'remaining_today' => 36,
                            'can_swipe' => true,
                            'stars_balance' => 5,
                            'super_like_cost' => 1,
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/swipes/likes-received',
                    'title' => 'See Who Liked You',
                    'auth' => true,
                    'description' => 'View users who swiped like or star on you. Blurred for free users, unlocked for premium.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Likes received retrieved.',
                        'data' => [
                            'is_premium' => true,
                            'total_likes' => 12,
                            'likes' => [
                                [
                                    'id' => 99,
                                    'type' => 'star',
                                    'sender' => ['id' => 45, 'name' => 'Sneha Gupta', 'age' => 26],
                                ]
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/matches',
                    'title' => 'List Matches',
                    'auth' => true,
                    'description' => 'List all active matches with partner profiles and chat timestamps.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Matches retrieved.',
                        'data' => [
                            [
                                'match_id' => 42,
                                'matched_at' => '2026-11-08T00:45:00Z',
                                'partner' => [
                                    'id' => 102,
                                    'name' => 'Ananya Verma',
                                    'age' => 25,
                                    'avatar' => 'https://...',
                                ],
                            ]
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/matches/{id}/unmatch',
                    'title' => 'Unmatch Profile',
                    'auth' => true,
                    'description' => 'Remove a mutual match with optional reason.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => ['reason' => 'Lost interest'],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Successfully unmatched.',
                        'code' => 200,
                    ],
                ],
            ],

            'Subscriptions & Premium' => [
                [
                    'method' => 'GET',
                    'path' => '/subscriptions/plans',
                    'title' => 'List Subscription Plans',
                    'auth' => false,
                    'description' => 'List active VIP subscription tiers with prices and features.',
                    'headers' => ['Accept' => 'application/json'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Subscription plans retrieved.',
                        'data' => [
                            [
                                'id' => 2,
                                'name' => 'Monthly Gold',
                                'slug' => 'monthly-gold',
                                'price' => 599.00,
                                'currency' => 'INR',
                                'duration_days' => 30,
                                'daily_swipe_limit' => -1,
                                'daily_star_allowance' => 10,
                                'can_see_likes' => true,
                                'can_rewind' => true,
                                'can_boost' => true,
                            ]
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/subscriptions/current',
                    'title' => 'Get Current Subscription',
                    'auth' => true,
                    'description' => 'Get details of authenticated user active premium plan.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Active subscription retrieved.',
                        'data' => [
                            'is_active' => true,
                            'plan' => ['name' => 'Monthly Gold'],
                            'expires_at' => '2026-12-08T00:00:00Z',
                        ],
                        'code' => 200,
                    ],
                ],
            ],

            'Stars & Wallet' => [
                [
                    'method' => 'GET',
                    'path' => '/stars/packages',
                    'title' => 'List Star Packages',
                    'auth' => false,
                    'description' => 'List available star packs with counts and pricing.',
                    'headers' => ['Accept' => 'application/json'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Star packages retrieved.',
                        'data' => [
                            ['id' => 3, 'name' => 'Superstar Pack', 'stars_count' => 60, 'price' => 799.00, 'badge' => 'MOST POPULAR']
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/stars/wallet',
                    'title' => 'Get Star Wallet Balance',
                    'auth' => true,
                    'description' => 'View current star balance and transaction logs.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Star wallet retrieved.',
                        'data' => [
                            'balance' => 45,
                            'transactions' => [
                                ['type' => 'credit', 'amount' => 25, 'description' => 'Purchased Crush Pack', 'balance_after' => 45],
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/stars/send',
                    'title' => 'Send Stars (Super Like Gift)',
                    'auth' => true,
                    'description' => 'Gift stars directly to another profile with an optional crush note.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => ['target_user_id' => 102, 'stars' => 3, 'message' => 'Loved your smile!'],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Successfully sent 3 Stars to Ananya Verma!',
                        'data' => ['stars_sent' => 3, 'new_balance' => 42],
                        'code' => 200,
                    ],
                ],
            ],

            'Razorpay Payments' => [
                [
                    'method' => 'POST',
                    'path' => '/payments/create-order',
                    'title' => 'Create Razorpay Order',
                    'auth' => true,
                    'description' => 'Generate Razorpay order for subscription plan or star pack.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'item_type' => 'subscription', // or 'star_package'
                        'item_id' => 2,
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Razorpay order created successfully.',
                        'data' => [
                            'order_id' => 'order_Kx87as9da8s',
                            'amount' => 599.00,
                            'amount_in_paise' => 59900,
                            'currency' => 'INR',
                            'key_id' => 'rzp_test_...',
                            'item' => ['type' => 'subscription', 'id' => 2, 'name' => 'Monthly Gold'],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/payments/verify',
                    'title' => 'Verify Payment Signature',
                    'auth' => true,
                    'description' => 'Verify Razorpay HMAC-SHA256 signature and fulfill purchase idempotently.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'razorpay_order_id' => 'order_Kx87as9da8s',
                        'razorpay_payment_id' => 'pay_Kx87bc9910d',
                        'razorpay_signature' => '9a8sd7f6a5s4df87as6d5f47as6d5f...',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Payment verified and fulfilled successfully.',
                        'data' => ['success' => true, 'payment_status' => 'captured'],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/payments/webhook',
                    'title' => 'Razorpay Webhook Handler',
                    'auth' => false,
                    'description' => 'Razorpay server webhook endpoint (validates X-Razorpay-Signature).',
                    'headers' => ['X-Razorpay-Signature' => '{signature}', 'Content-Type' => 'application/json'],
                    'body' => ['event' => 'payment.captured', 'payload' => '...'],
                    'response_example' => [
                        'status' => 'ok',
                        'result' => ['status' => 'success', 'event' => 'payment.captured'],
                    ],
                ],
            ],

            'Safety & Moderation' => [
                [
                    'method' => 'POST',
                    'path' => '/user/report',
                    'title' => 'Report Profile',
                    'auth' => true,
                    'description' => 'Report user profile for harassment, inappropriate content, fake account, or spam.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => [
                        'reported_user_id' => 102,
                        'reason' => 'fake_profile',
                        'details' => 'Using celebrity pictures',
                    ],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Report submitted successfully. Our safety team will review it.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/block',
                    'title' => 'Block Profile',
                    'auth' => true,
                    'description' => 'Block a user from appearing in discovery or messages.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => ['blocked_user_id' => 102],
                    'response_example' => [
                        'status' => true,
                        'message' => 'User blocked successfully.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/user/blocked',
                    'title' => 'List Blocked Users',
                    'auth' => true,
                    'description' => 'View all profiles blocked by the user.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Blocked users retrieved.',
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/user/unblock',
                    'title' => 'Unblock Profile',
                    'auth' => true,
                    'description' => 'Unblock a previously blocked profile.',
                    'headers' => ['Authorization' => 'Bearer {token}', 'Content-Type' => 'application/json'],
                    'body' => ['blocked_user_id' => 102],
                    'response_example' => [
                        'status' => true,
                        'message' => 'User unblocked successfully.',
                        'code' => 200,
                    ],
                ],
            ],

            'Notifications' => [
                [
                    'method' => 'GET',
                    'path' => '/notifications',
                    'title' => 'List Notifications',
                    'auth' => true,
                    'description' => 'Retrieve notifications for matches, likes, super likes, and subscriptions.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'Notifications retrieved.',
                        'data' => [
                            'unread_count' => 2,
                            'notifications' => [
                                [
                                    'id' => 1,
                                    'title' => 'It\'s a Match! 🎉',
                                    'body' => 'You and Ananya Verma liked each other.',
                                    'type' => 'match',
                                    'is_read' => false,
                                ]
                            ],
                        ],
                        'code' => 200,
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/notifications/read-all',
                    'title' => 'Mark All as Read',
                    'auth' => true,
                    'description' => 'Mark all unread notifications as read.',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'body' => [],
                    'response_example' => [
                        'status' => true,
                        'message' => 'All notifications marked as read.',
                        'code' => 200,
                    ],
                ],
            ],
        ];
    }
}
