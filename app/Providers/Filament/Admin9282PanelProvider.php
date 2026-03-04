<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\UserStationsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\View\PanelsRenderHook;

class Admin9282PanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin9282')
            ->path('admin9282')
            ->login()
            ->brandName(fn (): string => $this->resolveBrandName())
            ->brandLogo(fn (): ?HtmlString => $this->resolveBrandLogo())
            ->favicon(fn (): ?string => $this->resolveBrandFaviconUrl())
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                UserStationsWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): HtmlString => new HtmlString(
                    '<div class="fi-topbar-brand-logo">' .
                    ($this->resolveBrandLogo()?->toHtml() ?? e($this->resolveBrandName())) .
                    '</div>'
                ),
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

    protected function resolveBrandLogo(): ?HtmlString
    {
        $logoPath = public_path('images/lfs-logo.png');

        if (file_exists($logoPath)) {
            $logoUrl = asset('images/lfs-logo.png');

            return new HtmlString(
                '<img src="' . e($logoUrl) . '" alt="LFS" class="h-8">'
            );
        }

        return null;
    }

    protected function resolveBrandName(): string
    {
        return 'LFS';
    }

    protected function resolveBrandFaviconUrl(): ?string
    {
        $logoPath = public_path('images/lfs-logo.png');

        if (file_exists($logoPath)) {
            return asset('images/lfs-logo.png');
        }

        return null;
    }
}
