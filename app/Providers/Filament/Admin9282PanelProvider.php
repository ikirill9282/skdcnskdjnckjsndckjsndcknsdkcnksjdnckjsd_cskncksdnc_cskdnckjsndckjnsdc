<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\UserStationsWidget;
use App\Models\Company;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

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
                'primary' => Color::Amber,
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
        $company = $this->resolveBrandCompany();

        if (! $company || blank($company->logo)) {
            return null;
        }

        $disk = Storage::disk('public');
        $logoPath = $company->logo;

        if (! $disk->exists($logoPath)) {
            return null;
        }

        $logoUrl = $disk->url($logoPath);

        return new HtmlString(
            '<img src="' . e($logoUrl) . '" alt="' . e($company->name ?? 'Логотип') . '" class="h-8">'
        );
    }

    protected function resolveBrandName(): string
    {
        $company = $this->resolveBrandCompany();

        if ($company && filled($company->name)) {
            return $company->name;
        }

        return 'Вы будущее';
    }

    protected function resolveBrandCompany(): ?Company
    {
        static $resolved = false;
        static $company;

        if ($resolved) {
            return $company;
        }

        $resolved = true;

        $user = auth()->user();

        if (! $user) {
            return $company = null;
        }

        return $company = $user->companies()
            ->orderBy('companies.created_at')
            ->first();
    }

    protected function resolveBrandFaviconUrl(): ?string
    {
        $company = $this->resolveBrandCompany();

        if (! $company || blank($company->logo)) {
            return null;
        }

        $disk = Storage::disk('public');
        $logoPath = $company->logo;

        if (! $disk->exists($logoPath)) {
            return null;
        }

        return $disk->url($logoPath);
    }
}
