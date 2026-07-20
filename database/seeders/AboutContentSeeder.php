<?php

namespace Database\Seeders;

use App\Models\CoreValue;
use App\Models\Milestone;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class AboutContentSeeder extends Seeder
{
    public function run(): void
    {
        $milestones = [
            ['2020', 'Latechify Digital Hub (then Latechsolutions) founded with a mission to empower African tech talent', 'Sparkles'],
            ['2021', 'Launched first cohort with 25 students, partnering a cyber security firm in Ibadan', 'Zap'],
            ['2023', 'Expanded curriculum to include data science and mobile development tracks', 'TrendingUp'],
            ['2024', 'Opened new physical office campus and launched online learning platform', 'Globe'],
        ];
        foreach ($milestones as $i => [$year, $event, $icon]) {
            Milestone::create(['year' => $year, 'event' => $event, 'icon' => $icon, 'sort_order' => $i + 1]);
        }

        $values = [
            ['Innovation', 'We embrace new technologies and teaching methodologies to provide cutting-edge education.', 'Lightbulb'],
            ['Excellence', 'We strive for the highest standards in our curriculum, instruction, and student outcomes.', 'Award'],
            ['Accessibility', 'We believe quality tech education should be accessible to all motivated learners.', 'Globe'],
            ['Community', 'We foster a supportive environment where collaboration and networking thrive.', 'Users'],
        ];
        foreach ($values as $i => [$title, $desc, $icon]) {
            CoreValue::create(['title' => $title, 'description' => $desc, 'icon' => $icon, 'sort_order' => $i + 1]);
        }

        // About page stats band
        $about = [
            ['Students Trained', '15+', 'GraduationCap'],
            ['Expert Instructors', '5+', 'Users'],
            ['Course Completion', '95%', 'CheckCircle'],
            ['Lines of Code', '1M+', 'Code'],
        ];
        foreach ($about as $i => [$label, $value, $icon]) {
            Stat::create(['label' => $label, 'value' => $value, 'icon' => $icon, 'group' => 'about', 'sort_order' => $i + 1]);
        }

        // Testimonial section highlights
        $testimonial = [
            ['10+ Graduates', 'From diverse academic backgrounds', 'GraduationCap'],
            ['95% Success Rate', 'Career transitions into tech', 'Award'],
            ['24 Weeks Intensive', 'Practical curriculum with real projects', 'BookOpen'],
            ['Lifetime Mentorship', 'Ongoing support after graduation', 'UserCheck'],
        ];
        foreach ($testimonial as $i => [$value, $label, $icon]) {
            Stat::create(['label' => $label, 'value' => $value, 'icon' => $icon, 'group' => 'testimonial', 'sort_order' => $i + 1]);
        }
    }
}
