<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_render(): void
    {
        $urls = [
            '/', '/about', '/services', '/pricing', '/contact', '/courses',
            '/courses/frontend-web-development', '/apply', '/schedule-consultation',
            '/blog', '/terms', '/privacy', '/cookies', '/verify-certificate',
            '/summer-coding-camp',
        ];

        $failures = [];
        foreach ($urls as $url) {
            $status = $this->get($url)->status();
            if ($status >= 400) {
                $failures[] = "$url => $status";
            }
        }

        $this->assertEmpty($failures, "Public page failures:\n".implode("\n", $failures));
    }

    public function test_blog_detail_renders(): void
    {
        $slug = \App\Models\BlogPost::first()->slug;
        $this->get("/blog/{$slug}")->assertOk();
    }

    public function test_admin_panel_pages_render(): void
    {
        $admin = User::where('is_admin', true)->firstOrFail();

        // Collect all parameterless GET routes under the admin panel.
        $adminUrls = collect(Route::getRoutes())
            ->filter(fn ($r) => in_array('GET', $r->methods()) && str_starts_with($r->uri(), 'admin'))
            ->map(fn ($r) => $r->uri())
            ->reject(fn ($uri) => str_contains($uri, '{') || str_contains($uri, 'logout') || str_contains($uri, 'livewire'))
            ->unique()
            ->values();

        $failures = [];
        foreach ($adminUrls as $uri) {
            $status = $this->actingAs($admin)->get('/'.$uri)->status();
            if ($status >= 500) {
                $failures[] = "/$uri => $status";
            }
        }

        $this->assertEmpty($failures, "Admin page failures:\n".implode("\n", $failures));
        $this->assertGreaterThan(10, $adminUrls->count(), 'Expected many admin routes');
    }
}
