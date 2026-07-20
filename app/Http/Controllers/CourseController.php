<?php

namespace App\Http\Controllers;

use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::active()->get();

        $tags = $courses->flatMap(fn ($c) => $c->tags ?? [])->unique()->values();

        // Group by category, keeping a sensible order.
        $grouped = $courses->groupBy('category')->sortKeys();

        return view('pages.courses', compact('courses', 'tags', 'grouped'));
    }

    public function show(Course $course)
    {
        abort_unless($course->is_active, 404);

        $course->load([
            'highlights',
            'modules.topics.resources',
            'faqs',
        ]);

        return view('pages.course-detail', compact('course'));
    }
}
