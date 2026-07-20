<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        // Technologies & tools taught across the curriculum (rendered as a marquee).
        $technologies = [
            'JavaScript', 'TypeScript', 'React', 'Node.js', 'Python', 'PHP', 'Laravel',
            'React Native', 'Flutter', 'Figma', 'Tailwind CSS', 'MongoDB', 'MySQL',
            'AWS', 'Docker', 'Git', 'Next.js', 'Express',
        ];

        foreach ($technologies as $i => $name) {
            Partner::updateOrCreate(
                ['name' => $name, 'category' => 'partner'],
                ['sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
