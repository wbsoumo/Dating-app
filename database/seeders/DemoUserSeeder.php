<?php

namespace Database\Seeders;

use App\Models\Interest;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\StarWallet;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserPreference;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(int $count = 2020): void
    {
        $maleFirstNames = [
            'Aarav', 'Rohan', 'Vihaan', 'Aditya', 'Arjun', 'Kabir', 'Aryan', 'Reyansh', 'Dev', 'Kunal',
            'Rahul', 'Vikram', 'Siddharth', 'Amit', 'Yash', 'Gaurav', 'Nikhil', 'Pranav', 'Varun', 'Ayush',
            'Kartik', 'Mayank', 'Harsh', 'Rishabh', 'Akash', 'Sameer', 'Dhruv', 'Anand', 'Manish', 'Karan',
            'Alok', 'Abhishek', 'Rajat', 'Deepak', 'Tarun', 'Sanjay', 'Rohit', 'Suraj', 'Vikas', 'Chirag'
        ];

        $femaleFirstNames = [
            'Ananya', 'Diya', 'Ishita', 'Riya', 'Priya', 'Tanvi', 'Shreya', 'Sneha', 'Pooja', 'Neha',
            'Kavya', 'Simran', 'Meera', 'Anjali', 'Radhika', 'Aditi', 'Kriti', 'Divya', 'Aishwarya', 'Swati',
            'Naina', 'Khushi', 'Tara', 'Disha', 'Isha', 'Trisha', 'Sanjana', 'Payal', 'Muskan', 'Sonali',
            'Pallavi', 'Ritu', 'Bhavna', 'Shikha', 'Aarti', 'Komal', 'Prachi', 'Garima', 'Mansi', 'Juhi'
        ];

        $lastNames = [
            'Sharma', 'Verma', 'Gupta', 'Patel', 'Mehta', 'Singh', 'Joshi', 'Rao', 'Nair', 'Reddy',
            'Kumar', 'Das', 'Sen', 'Banerjee', 'Chatterjee', 'Kapoor', 'Malhotra', 'Bhatia', 'Agarwal', 'Mishra',
            'Deshmukh', 'Kulkarni', 'Chopra', 'Saxena', 'Trivedi', 'Pandey', 'Choudhary', 'Ghosh', 'Iyer', 'Menon'
        ];

        $cities = [
            ['city' => 'New Delhi', 'state' => 'Delhi', 'lat' => 28.6139, 'lng' => 77.2090],
            ['city' => 'Mumbai', 'state' => 'Maharashtra', 'lat' => 19.0760, 'lng' => 72.8777],
            ['city' => 'Bengaluru', 'state' => 'Karnataka', 'lat' => 12.9716, 'lng' => 77.5946],
            ['city' => 'Hyderabad', 'state' => 'Telangana', 'lat' => 17.3850, 'lng' => 78.4867],
            ['city' => 'Kolkata', 'state' => 'West Bengal', 'lat' => 22.5726, 'lng' => 88.3639],
            ['city' => 'Chennai', 'state' => 'Tamil Nadu', 'lat' => 13.0827, 'lng' => 80.2707],
            ['city' => 'Pune', 'state' => 'Maharashtra', 'lat' => 18.5204, 'lng' => 73.8567],
            ['city' => 'Jaipur', 'state' => 'Rajasthan', 'lat' => 26.9124, 'lng' => 75.7873],
            ['city' => 'Ahmedabad', 'state' => 'Gujarat', 'lat' => 23.0225, 'lng' => 72.5714],
            ['city' => 'Chandigarh', 'state' => 'Punjab', 'lat' => 30.7333, 'lng' => 76.7794],
            ['city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'lat' => 26.8467, 'lng' => 80.9462],
            ['city' => 'Kochi', 'state' => 'Kerala', 'lat' => 9.9312, 'lng' => 76.2673],
            ['city' => 'Goa', 'state' => 'Goa', 'lat' => 15.2993, 'lng' => 74.1240],
            ['city' => 'Indore', 'state' => 'Madhya Pradesh', 'lat' => 22.7196, 'lng' => 75.8577],
        ];

        $occupations = [
            'Software Engineer', 'Product Designer', 'Data Scientist', 'Architect', 'Doctor',
            'Chartered Accountant', 'Marketing Strategist', 'Content Creator', 'Management Consultant',
            'Financial Analyst', 'Graphic Designer', 'Photographer', 'Entrepreneur', 'Lawyer',
            'UX Researcher', 'Journalist', 'Professor', 'Civil Engineer', 'Pilot', 'Chef'
        ];

        $educations = [
            'IIT Delhi', 'BITS Pilani', 'Delhi University', 'St. Xavier\'s College Mumbai',
            'IIM Bangalore', 'Symbiosis Pune', 'NIFT Mumbai', 'SRM University', 'Anna University',
            'Jadavpur University', 'Christ University Bangalore', 'NMIMS Mumbai', 'Amity University'
        ];

        $maleBios = [
            'Software engineer by day, foodie by night. Love exploring indie cafes and weekend road trips.',
            'Architect who appreciates good design, espresso, and deep late-night conversations.',
            'Gym regular, cricket fanatic, and amateur chef. Looking for someone genuine to share adventures with.',
            'Doctor living in Mumbai. Passionate about live music, books, and finding someone who doesn\'t take life too seriously.',
            'Building tech startups. Always up for filter coffee, podcasts, and mountain treks.',
            'Creative director who loves cinema, dogs, and spontaneous road journeys.',
            'Finance nerd with a love for indie music and photography. Let\'s grab coffee and talk about everything.',
            'Always curious, loves stargazing, traveling, and cooking Italian dinners.',
        ];

        $femaleBios = [
            'Product designer & coffee enthusiast. Probably reading a thriller or trying a new sourdough recipe.',
            'Yoga practitioner, dog mom, and avid traveler. Looking for meaningful connection and plenty of laughter.',
            'Doctor by profession, passionate about classical dance, books, and exploring quiet hill stations.',
            'Digital marketer who loves sunsets, live stand-up comedy, and making aesthetic playlists.',
            'Architectural enthusiast with a weakness for street food and old jazz records.',
            'Lawyer in court, sweet tooth at home. Looking for a partner in crime for weekend brunches and museum visits.',
            'Fashion stylist & photography lover. Believer in kindness, good energy, and authentic bonds.',
            'Content creator & traveler. Seeking someone with ambition, empathy, and great humor.',
        ];

        $malePhotos = [
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1480429370139-e0132c086e2a?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
        ];

        $femalePhotos = [
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1548142813-c348350df52b?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=800&auto=format&fit=crop&q=80',
        ];

        $religions = ['Hindu', 'Muslim', 'Sikh', 'Christian', 'Jain', 'Spiritual', 'Agnostic'];
        $relationshipGoals = ['long_term', 'marriage', 'short_term', 'casual', 'friendship'];
        $habits = ['never', 'socially', 'frequently'];
        $zodiacSigns = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];

        $allInterestIds = Interest::pluck('id')->toArray();
        if (empty($allInterestIds)) {
            $this->call(InterestSeeder::class);
            $allInterestIds = Interest::pluck('id')->toArray();
        }

        $passwordHash = Hash::make('DemoUser@123');
        $batchSize = 200;
        $totalBatches = (int) ceil($count / $batchSize);

        $now = Carbon::now();

        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $currentBatchCount = min($batchSize, $count - ($batch * $batchSize));
            DB::transaction(function () use (
                $batch,
                $currentBatchCount,
                $maleFirstNames,
                $femaleFirstNames,
                $lastNames,
                $cities,
                $occupations,
                $educations,
                $maleBios,
                $femaleBios,
                $malePhotos,
                $femalePhotos,
                $religions,
                $relationshipGoals,
                $habits,
                $zodiacSigns,
                $allInterestIds,
                $passwordHash,
                $now
            ) {
                for ($i = 0; $i < $currentBatchCount; $i++) {
                    $index = ($batch * 200) + $i + 1;
                    $isMale = ($index % 2 === 1);
                    $gender = $isMale ? 'male' : 'female';

                    $firstName = $isMale
                        ? $maleFirstNames[array_rand($maleFirstNames)]
                        : $femaleFirstNames[array_rand($femaleFirstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $name = "{$firstName} {$lastName}";

                    $age = random_int(21, 36);
                    $dob = Carbon::now()->subYears($age)->subDays(random_int(0, 364))->toDateString();

                    $phone = '91' . str_pad((string) (9800000000 + $index), 10, '0', STR_PAD_LEFT);
                    $email = "demo.user{$index}@dilsematch.com";

                    $isVerified = (random_int(1, 100) <= 35);
                    $isPremium = (random_int(1, 100) <= 15);

                    $userId = DB::table('users')->insertGetId([
                        'name' => $name,
                        'phone' => $phone,
                        'phone_verified_at' => $now,
                        'email' => $email,
                        'email_verified_at' => $now,
                        'password' => $passwordHash,
                        'gender' => $gender,
                        'dob' => $dob,
                        'age' => $age,
                        'status' => 'active',
                        'is_verified' => $isVerified,
                        'verification_submitted_at' => $isVerified ? $now : null,
                        'is_premium' => $isPremium,
                        'premium_expires_at' => $isPremium ? $now->copy()->addMonths(random_int(1, 6)) : null,
                        'custom_swipe_limit' => null,
                        'is_demo' => true,
                        'last_active_at' => $now->copy()->subMinutes(random_int(5, 7200)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Profile
                    $bio = $isMale
                        ? $maleBios[array_rand($maleBios)]
                        : $femaleBios[array_rand($femaleBios)];

                    DB::table('profiles')->insert([
                        'user_id' => $userId,
                        'bio' => $bio,
                        'occupation' => $occupations[array_rand($occupations)],
                        'education' => $educations[array_rand($educations)],
                        'height' => $isMale ? random_int(168, 188) : random_int(152, 175),
                        'religion' => $religions[array_rand($religions)],
                        'relationship_goal' => $relationshipGoals[array_rand($relationshipGoals)],
                        'drinking' => $habits[array_rand($habits)],
                        'smoking' => $habits[array_rand($habits)],
                        'workout' => $habits[array_rand($habits)],
                        'zodiac_sign' => $zodiacSigns[array_rand($zodiacSigns)],
                        'hometown' => $cities[array_rand($cities)]['city'],
                        'languages' => json_encode(['English', 'Hindi']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Location with jitter
                    $cityData = $cities[array_rand($cities)];
                    $jitterLat = (random_int(-150, 150) / 1000.0);
                    $jitterLng = (random_int(-150, 150) / 1000.0);

                    DB::table('user_locations')->insert([
                        'user_id' => $userId,
                        'latitude' => round($cityData['lat'] + $jitterLat, 7),
                        'longitude' => round($cityData['lng'] + $jitterLng, 7),
                        'city' => $cityData['city'],
                        'state' => $cityData['state'],
                        'country' => 'India',
                        'address' => "{$cityData['city']}, {$cityData['state']}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Photos (2 photos per user)
                    $photoPool = $isMale ? $malePhotos : $femalePhotos;
                    $primaryPhotoUrl = $photoPool[array_rand($photoPool)];
                    $secondPhotoUrl = $photoPool[array_rand($photoPool)];

                    DB::table('photos')->insert([
                        [
                            'user_id' => $userId,
                            'url' => $primaryPhotoUrl,
                            'path' => null,
                            'thumbnail_url' => $primaryPhotoUrl,
                            'is_primary' => true,
                            'sort_order' => 0,
                            'is_approved' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'user_id' => $userId,
                            'url' => $secondPhotoUrl,
                            'path' => null,
                            'thumbnail_url' => $secondPhotoUrl,
                            'is_primary' => false,
                            'sort_order' => 1,
                            'is_approved' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);

                    // Preferences
                    DB::table('user_preferences')->insert([
                        'user_id' => $userId,
                        'gender_preference' => $isMale ? 'female' : 'male',
                        'min_age' => max(18, $age - 5),
                        'max_age' => min(60, $age + 6),
                        'max_distance_km' => 50,
                        'preferred_religion' => null,
                        'preferred_relationship_goal' => null,
                        'show_verified_only' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Star wallet
                    DB::table('star_wallets')->insert([
                        'user_id' => $userId,
                        'balance' => $isPremium ? 25 : 5,
                        'last_daily_bonus_at' => $now->toDateString(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // 3 to 5 random interests
                    if (!empty($allInterestIds)) {
                        $numInterests = random_int(3, 5);
                        $pickedInterests = array_rand(array_flip($allInterestIds), $numInterests);
                        $pivotRecords = [];
                        foreach ((array) $pickedInterests as $interestId) {
                            $pivotRecords[] = [
                                'user_id' => $userId,
                                'interest_id' => $interestId,
                            ];
                        }
                        DB::table('interest_user')->insert($pivotRecords);
                    }
                }
            });
        }
    }
}
