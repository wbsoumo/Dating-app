<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            // Lifestyle & Leisure
            ['name' => 'Travel & Exploring', 'icon' => 'fa-plane-departure', 'category' => 'Lifestyle'],
            ['name' => 'Coffee & Cafes', 'icon' => 'fa-coffee', 'category' => 'Lifestyle'],
            ['name' => 'Foodie & Street Food', 'icon' => 'fa-utensils', 'category' => 'Lifestyle'],
            ['name' => 'Cooking & Baking', 'icon' => 'fa-blender', 'category' => 'Lifestyle'],
            ['name' => 'Dog Lover', 'icon' => 'fa-dog', 'category' => 'Lifestyle'],
            ['name' => 'Cat Lover', 'icon' => 'fa-cat', 'category' => 'Lifestyle'],
            ['name' => 'Nightlife & Clubbing', 'icon' => 'fa-cocktail', 'category' => 'Lifestyle'],
            ['name' => 'Road Trips', 'icon' => 'fa-car-side', 'category' => 'Lifestyle'],

            // Sports & Fitness
            ['name' => 'Gym & Workout', 'icon' => 'fa-dumbbell', 'category' => 'Fitness'],
            ['name' => 'Yoga & Meditation', 'icon' => 'fa-spa', 'category' => 'Fitness'],
            ['name' => 'Cricket', 'icon' => 'fa-baseball-bat-ball', 'category' => 'Fitness'],
            ['name' => 'Football / Soccer', 'icon' => 'fa-futbol', 'category' => 'Fitness'],
            ['name' => 'Badminton', 'icon' => 'fa-table-tennis-paddle-ball', 'category' => 'Fitness'],
            ['name' => 'Swimming', 'icon' => 'fa-person-swimming', 'category' => 'Fitness'],
            ['name' => 'Running & Marathons', 'icon' => 'fa-person-running', 'category' => 'Fitness'],
            ['name' => 'Hiking & Trekking', 'icon' => 'fa-mountain', 'category' => 'Fitness'],

            // Creativity & Arts
            ['name' => 'Photography', 'icon' => 'fa-camera-retro', 'category' => 'Creativity'],
            ['name' => 'Live Music & Concerts', 'icon' => 'fa-guitar', 'category' => 'Creativity'],
            ['name' => 'Art & Painting', 'icon' => 'fa-palette', 'category' => 'Creativity'],
            ['name' => 'Writing & Poetry', 'icon' => 'fa-pen-nib', 'category' => 'Creativity'],
            ['name' => 'Fashion & Styling', 'icon' => 'fa-shirt', 'category' => 'Creativity'],
            ['name' => 'Dancing', 'icon' => 'fa-shoe-prints', 'category' => 'Creativity'],

            // Entertainment & Pop Culture
            ['name' => 'Bollywood & Cinema', 'icon' => 'fa-film', 'category' => 'Entertainment'],
            ['name' => 'Binge Watching Series', 'icon' => 'fa-tv', 'category' => 'Entertainment'],
            ['name' => 'Stand-up Comedy', 'icon' => 'fa-face-laugh-beam', 'category' => 'Entertainment'],
            ['name' => 'Reading & Books', 'icon' => 'fa-book-open', 'category' => 'Entertainment'],
            ['name' => 'Anime & Manga', 'icon' => 'fa-dragon', 'category' => 'Entertainment'],
            ['name' => 'Gaming & Esports', 'icon' => 'fa-gamepad', 'category' => 'Entertainment'],
            ['name' => 'Podcasts', 'icon' => 'fa-microphone', 'category' => 'Entertainment'],

            // Tech & Ambition
            ['name' => 'Startups & Business', 'icon' => 'fa-rocket', 'category' => 'Ambition'],
            ['name' => 'Tech & Coding', 'icon' => 'fa-laptop-code', 'category' => 'Ambition'],
            ['name' => 'Stock Market & Crypto', 'icon' => 'fa-chart-line', 'category' => 'Ambition'],
            ['name' => 'Volunteering & Social Work', 'icon' => 'fa-hands-holding-heart', 'category' => 'Ambition'],
        ];

        foreach ($interests as $item) {
            Interest::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'icon' => $item['icon'],
                    'category' => $item['category'],
                ]
            );
        }
    }
}
