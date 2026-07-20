<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'frontend-web-development'  => 'Software Engineering',
            'backend-development'       => 'Software Engineering',
            'fullstack-development'     => 'Software Engineering',
            'mobile-app-development'    => 'Software Engineering',
            'data-science-fundamentals' => 'Other Tech',
            'ui-ux-design'              => 'Other Tech',
        ];

        foreach ($map as $slug => $category) {
            Course::where('slug', $slug)->update(['category' => $category]);
        }
    }
}
