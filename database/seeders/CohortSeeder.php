<?php

namespace Database\Seeders;

use App\Models\CohortActivity;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class CohortSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['Active Students', '10+', 'Users'],
            ['Expert Instructors', '5', 'BookOpen'],
            ['Weekly Sessions', '4', 'Calendar'],
            ['Completion Rate', '95%', 'Trophy'],
        ];
        foreach ($stats as $i => [$label, $value, $icon]) {
            Stat::create(['label' => $label, 'value' => $value, 'icon' => $icon, 'group' => 'cohort', 'sort_order' => $i + 1]);
        }

        $activities = [
            ['Live Coding Sessions', 'Participate in real-time collaborative coding sessions with instructors and peers.', 'Code'],
            ['Hands-on Projects', 'Build real-world applications that solve practical problems and showcase your skills.', 'Monitor'],
            ['Peer Review Sessions', 'Get constructive feedback from peers and instructors to improve your coding skills.', 'CheckCircle'],
            ['Tech Talks & Workshops', 'Attend specialized workshops and talks by industry professionals on cutting-edge technologies.', 'Star'],
            ['Career Mentorship', 'Receive guidance on career paths, interview preparation, and portfolio development.', 'Target'],
            ['Hackathons', 'Participate in regular hackathons to solve real-world problems and win exciting prizes.', 'Zap'],
        ];
        foreach ($activities as $i => [$title, $desc, $icon]) {
            CohortActivity::create(['title' => $title, 'description' => $desc, 'icon' => $icon, 'sort_order' => $i + 1]);
        }
    }
}
