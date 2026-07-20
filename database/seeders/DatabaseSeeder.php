<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin account for the Filament panel (/admin)
        User::updateOrCreate(
            ['email' => 'latechify2024@gmail.com'],
            [
                'name'       => 'Latechify Admin',
                'first_name' => 'Latechify',
                'last_name'  => 'Admin',
                'password'   => Hash::make('password'),
                'is_admin'   => true,
            ]
        );

        $this->call([
            SettingsSeeder::class,
            MenuItemSeeder::class,
            HeroSlideSeeder::class,
            AdvertSeeder::class,
            ServiceSeeder::class,
            CourseSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            AboutContentSeeder::class,
            CohortSeeder::class,
            PageSeeder::class,
            BlogSeeder::class,
            BenefitSeeder::class,
            PartnerSeeder::class,
            CertificateSeeder::class,
            CourseCategorySeeder::class,
        ]);
    }
}
