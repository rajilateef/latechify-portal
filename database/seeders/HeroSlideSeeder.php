<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'subtitle'    => 'Learn, Build, and Innovate with Expert Guidance',
                'title'       => 'Empowering Your Digital Journey',
                'description' => 'Jumpstart your career in tech with our comprehensive bootcamps and courses designed to equip you with in-demand skills.',
                'button_text' => 'Explore Courses',
                'button_link' => '/courses',
                'image'       => 'assets/imgs/workspace_1.jpg',
            ],
            [
                'subtitle'    => 'Custom Digital Solutions for Modern Enterprises',
                'title'       => 'Transform Your Business with Technology',
                'description' => 'From web applications to mobile solutions, we help businesses leverage technology to achieve their goals.',
                'button_text' => 'Our Services',
                'button_link' => '/services',
                'image'       => 'assets/imgs/session2.jpg',
            ],
            [
                'subtitle'    => 'Connect, Collaborate, and Grow Together',
                'title'       => 'Join Our Tech Community',
                'description' => 'Be part of a vibrant community of learners, professionals, and innovators who share your passion for technology.',
                'button_text' => 'Apply Now',
                'button_link' => '/apply',
                'image'       => 'assets/imgs/slider.jpg',
            ],
        ];

        foreach ($slides as $i => $slide) {
            HeroSlide::create([...$slide, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
