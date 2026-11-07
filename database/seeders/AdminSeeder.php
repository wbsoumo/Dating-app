<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@datingapp.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('Admin@123456'),
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'moderator@datingapp.com'],
            [
                'name' => 'Profile Moderator',
                'password' => Hash::make('Moderator@123456'),
                'role' => 'moderator',
                'status' => 'active',
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'finance@datingapp.com'],
            [
                'name' => 'Financial Manager',
                'password' => Hash::make('Finance@123456'),
                'role' => 'financial_admin',
                'status' => 'active',
            ]
        );
    }
}
