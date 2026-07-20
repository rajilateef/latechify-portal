<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name'        => 'Abdulhafeez',
                'designation' => 'MBBS Student during Fullstack',
                'quote'       => "Balancing medical school with Fullstack development was tough, but this program made it possible. The hands-on projects and supportive community helped me build real-world tech skills I never thought I'd master alongside my MBBS studies.",
                'avatar'      => 'assets/imgs/abdulhafeez.jpg',
            ],
            [
                'name'        => 'Warith',
                'designation' => 'Agric Engineering Graduate during Fullstack',
                'quote'       => "As an Agric Engineering grad, I wasn't sure how coding fit into my future—until this program. It opened my eyes to solving agricultural challenges with tech, and now I'm building tools that could transform the industry.",
                'avatar'      => 'assets/imgs/warith.jpg',
            ],
            [
                'name'        => 'Emmanuel',
                'designation' => 'Chemistry Graduate during Fullstack',
                'quote'       => "Coming from a Chemistry background, I thought coding was out of reach. This program broke it down step-by-step, and now I'm creating applications that blend science and tech—skills I'll carry into my career.",
                'avatar'      => 'assets/imgs/emmanuel.jpg',
            ],
        ];

        foreach ($items as $i => $item) {
            Testimonial::create([
                ...$item,
                'rating'       => 5,
                'category'     => 'Fullstack',
                'sort_order'   => $i + 1,
                'is_published' => true,
            ]);
        }
    }
}
