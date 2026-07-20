<?php

namespace Database\Seeders;

use App\Models\Benefit;
use Illuminate\Database\Seeder;

class BenefitSeeder extends Seeder
{
    public function run(): void
    {
        $benefits = [
            ['Industry-Expert Instructors', 'Learn from professionals with years of real-world experience building software for real companies.', 'GraduationCap'],
            ['Hands-On, Project-Based Learning', 'Build a portfolio of real applications, not just theory — you learn by shipping.', 'Code'],
            ['Job-Ready Curriculum', 'Every module is built around the exact skills employers are hiring for today.', 'Briefcase'],
            ['Flexible Learning Options', 'Study online or in-person, with weekday and weekend cohorts that fit your schedule.', 'Calendar'],
            ['Lifetime Mentorship & Community', 'Ongoing mentorship and a supportive network that lasts long after graduation.', 'Users'],
            ['Verifiable Certification', 'Graduate with a certificate employers can verify online in seconds.', 'Award'],
            ['Career & Placement Support', 'Resume reviews, interview prep and introductions to our hiring partners.', 'Target'],
            ['Affordable & Installment Plans', 'World-class training at accessible prices, with flexible payment options.', 'Wallet'],
        ];

        foreach ($benefits as $i => [$title, $desc, $icon]) {
            Benefit::updateOrCreate(
                ['title' => $title],
                ['description' => $desc, 'icon' => $icon, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
