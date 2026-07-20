<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /**
     * Get a site setting value (cached), with an optional default.
     */
    function setting(string $key, $default = null)
    {
        return Setting::value($key, $default);
    }
}

if (! function_exists('tech_icon')) {
    /**
     * Look up official brand-icon path data (Simple Icons) for a technology
     * name. Returns ['hex' => '#rrggbb', 'path' => '...'] or null when unknown.
     * Matching is loose: "Node.js", "nodejs" and "NODE JS" all resolve.
     */
    function tech_icon(?string $name): ?array
    {
        static $map = null;
        if ($map === null) {
            $map = require __DIR__.'/tech_icons.php';
        }

        $key = preg_replace('/[^a-z0-9]/', '', strtolower((string) $name));

        return $map[$key] ?? null;
    }
}

if (! function_exists('lucide_name')) {
    /**
     * Convert a stored PascalCase/camelCase lucide icon name (e.g. "GraduationCap")
     * to the kebab-case name lucide's data-lucide attribute expects ("graduation-cap").
     */
    function lucide_name(?string $name, string $default = 'circle'): string
    {
        if (blank($name)) {
            return $default;
        }

        return \Illuminate\Support\Str::kebab($name);
    }
}

if (! function_exists('media_url')) {
    /**
     * Resolve an image/media path to a usable URL.
     * Handles absolute URLs, files on the public storage disk (admin uploads
     * + mirrored seed assets), and files living directly under public/.
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (blank($path)) {
            return $fallback ? media_url($fallback) : null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Root-relative so it works on any host (dev server, Herd, production).
        $relative = fn (string $url) => parse_url($url, PHP_URL_PATH) ?: $url;

        if (file_exists(public_path($path))) {
            return $relative(asset($path));
        }

        return $relative(Storage::disk('public')->url($path));
    }
}
