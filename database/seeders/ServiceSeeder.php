<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title'          => 'Coding Training',
                'icon'           => 'Code',
                'description'    => 'Comprehensive courses covering modern web and mobile development technologies, taught by industry experts.',
                'media_position' => 'bottom',
                'media_accent'   => 'plain',
                'images'         => ['assets/imgs/session.jpg', 'assets/imgs/session2.jpg', 'assets/imgs/training2.jpg'],
            ],
            [
                'title'          => 'Web Development',
                'icon'           => 'Layout',
                'description'    => 'Custom-built responsive websites and web applications optimized for performance and user experience.',
                'media_position' => 'bottom',
                'media_accent'   => 'blue',
                'images'         => ['images/laptop.jpg', 'images/desk.jpg', 'images/codebg.jpg'],
            ],
            [
                'title'          => 'Mobile App Development',
                'icon'           => 'Smartphone',
                'description'    => 'Native and cross-platform mobile applications for iOS and Android, tailored to your specific requirements.',
                'media_position' => 'top',
                'media_accent'   => 'plain',
                'images'         => ['assets/imgs/g_s.jpg', 'assets/imgs/working.jpg'],
            ],
            [
                'title'          => 'Backend Services',
                'icon'           => 'Database',
                'description'    => 'Scalable API development, database design, and cloud integration for robust digital solutions.',
                'media_position' => 'bottom',
                'media_accent'   => 'dark',
                'images'         => ['images/codebg.jpg', 'assets/imgs/bg_tech.jpg'],
            ],
            [
                'title'          => 'Digital Consulting',
                'icon'           => 'Layers',
                'description'    => 'Strategic technology advisory to help businesses leverage digital solutions for growth and efficiency.',
                'media_position' => 'top',
                'media_accent'   => 'blue',
                'images'         => ['images/teamwork.jpg', 'assets/imgs/signpost.jpg'],
            ],
            [
                'title'          => 'E-Learning Resources',
                'icon'           => 'Video',
                'description'    => 'Access to a library of on-demand video courses, learning materials, and coding challenges.',
                'media_position' => 'bottom',
                'media_accent'   => 'plain',
                'images'         => ['images/desk2.jpg', 'assets/imgs/rear_view.jpg', 'assets/imgs/IMG_20241113_175020.jpg'],
            ],
        ];

        foreach ($services as $i => $service) {
            Service::updateOrCreate(
                ['title' => $service['title']],
                [
                    ...$service,
                    'slug'       => Str::slug($service['title']),
                    'sort_order' => $i + 1,
                    'is_active'  => true,
                ]
            );
        }
    }
}
