<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $img = fn ($f) => "lovable-uploads/$f";

        $posts = [
            ['The Future of Web Development: Trends to Watch in 2025', 'Discover the emerging technologies and methodologies that are reshaping the landscape of web development.', 'Web Development', 'Michael Chen', '84371367-774f-4e18-97c1-45f575e8e600.png', '2025-04-22'],
            ['Mobile First Design: Best Practices for Modern Applications', 'Learn why designing for mobile first can lead to better user experiences across all devices and how to implement this approach effectively.', 'Mobile Development', 'Sarah Johnson', '3bb270e0-a037-43c9-9cb1-2b720bd42bc0.png', '2025-04-15'],
            ['Mastering JavaScript: Advanced Techniques for Modern Developers', 'Explore advanced JavaScript concepts and techniques that can take your development skills to the next level.', 'Programming', 'David Wilson', '6ff8ea3b-2a50-4d3a-94e1-f77f62621f76.png', '2025-04-08'],
            ['The Importance of User Experience in Tech Education', 'Why UX design principles are crucial for creating effective learning experiences in tech education platforms.', 'Education', 'Ana Rodriguez', '58abbe28-b49c-4927-b7f4-b0360491089b.png', '2025-04-01'],
            ['Building Secure Web Applications: A Comprehensive Guide', 'Security best practices every developer should know to protect web applications from common vulnerabilities.', 'Security', 'James Park', '71d64f3b-6367-46dd-89bf-a96980a2e6a9.png', '2025-03-25'],
            ['Data Visualization Techniques for Effective Storytelling', 'How to use data visualization to communicate complex information clearly and effectively.', 'Data Science', 'Sophia Martinez', '53433a39-e186-4d24-8074-8a28eba8cad6.png', '2025-03-18'],
        ];

        foreach ($posts as $i => [$title, $excerpt, $category, $author, $image, $date]) {
            BlogPost::create([
                'title'          => $title,
                'slug'           => Str::slug($title),
                'excerpt'        => $excerpt,
                'body'           => "<p>{$excerpt}</p><p>Our team at Latechify Digital Hub regularly shares insights, tutorials, and industry news to help you stay ahead in tech. This article dives deeper into the topic and what it means for learners and businesses alike.</p><p>Stay tuned for more practical guides from our instructors and community.</p>",
                'featured_image' => $img($image),
                'category'       => $category,
                'author_name'    => $author,
                'published_at'   => $date,
                'is_published'   => true,
            ]);
        }
    }
}
