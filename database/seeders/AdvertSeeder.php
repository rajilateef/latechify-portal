<?php

namespace Database\Seeders;

use App\Models\Advert;
use Illuminate\Database\Seeder;

class AdvertSeeder extends Seeder
{
    public function run(): void
    {
        $adverts = [
            [
                'title'       => 'New Cohort Enrollment Now Open',
                'image'       => 'assets/imgs/training.jpg',
                'link_url'    => '/apply',
                'description' => 'Secure your spot in our next Fullstack cohort — limited seats available.',
                'placement'   => 'both',
                'sort_order'  => 1,
            ],
            [
                'title'         => 'Save Up To 20% On Online Courses',
                'image'         => 'assets/imgs/cohort.jpg',
                'link_url'      => '/pricing',
                'description'   => 'Enroll online and pay less. Same expert-led curriculum, from anywhere.',
                'placement'     => 'section',
                'show_floating' => true,
                'sort_order'    => 2,
            ],
            [
                'title'       => 'Book a Free Career Consultation',
                'image'       => 'assets/imgs/banner.jpg',
                'link_url'    => '/schedule-consultation',
                'description' => 'Not sure where to start? Talk to our advisors, free of charge.',
                'placement'   => 'section',
                'sort_order'  => 3,
            ],
        ];

        foreach ($adverts as $advert) {
            Advert::updateOrCreate(['title' => $advert['title']], [...$advert, 'is_active' => true]);
        }
    }
}
