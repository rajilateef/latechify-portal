<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Home',        'type' => 'link',     'url' => '/',                   'icon' => null,  'highlight' => false],
            ['label' => 'About',       'type' => 'link',     'url' => '/about',              'icon' => null,  'highlight' => false],
            ['label' => 'Services',    'type' => 'services', 'url' => '/services',           'icon' => null,  'highlight' => false],
            ['label' => 'Courses',     'type' => 'courses',  'url' => '/courses',            'icon' => null,  'highlight' => false],
            ['label' => 'Summer Camp', 'type' => 'link',     'url' => '/summer-coding-camp', 'icon' => 'Sun', 'highlight' => true],
            ['label' => 'Pricing',     'type' => 'link',     'url' => '/pricing',            'icon' => null,  'highlight' => false],
            ['label' => 'Contact',     'type' => 'link',     'url' => '/contact',            'icon' => null,  'highlight' => false],
        ];

        foreach ($items as $i => $item) {
            MenuItem::updateOrCreate(
                ['label' => $item['label']],
                [...$item, 'sort_order' => $i + 1, 'is_visible' => true],
            );
        }
    }
}
