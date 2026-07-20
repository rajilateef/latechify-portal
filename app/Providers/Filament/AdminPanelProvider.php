<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Latechify Admin')
            ->colors([
                'primary' => Color::hex('#031273'),
            ])
            ->navigationGroups([
                'Home Page',
                'Courses & Services',
                'About & Content',
                'Submissions',
                'Settings',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                Widgets\AccountWidget::class,
            ])
            ->plugin(\TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make())
            // "Back to Media Library" link when browsing inside a folder.
            ->renderHook(
                \Filament\View\PanelsRenderHook::PAGE_START,
                fn (): string => \Illuminate\Support\Facades\Blade::render(
                    '<a href="{{ $url }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">'
                    .'<x-filament::icon icon="heroicon-m-arrow-left" class="h-4 w-4" /> Back to Media Library</a>',
                    ['url' => \TomatoPHP\FilamentMediaManager\Resources\FolderResource::getUrl('index')],
                ),
                scopes: \TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia::class,
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
