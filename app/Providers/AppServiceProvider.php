<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\PetugasPolicy;
use App\Services\Settings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, PetugasPolicy::class);

        $this->shareSettingsToLayouts();
    }

    /**
     * Bagikan data instansi dan tampilan ke seluruh layout sehingga
     * branding (logo, nama, kode) dan tema diterapkan secara global.
     */
    private function shareSettingsToLayouts(): void
    {
        View::composer(['layouts.app', 'layouts.guest', 'auth.login'], function (ViewInstance $view): void {
            $settings = app(Settings::class);

            $instansi = array_merge(Settings::defaults()['instansi'], $settings->get('instansi', Settings::defaults()['instansi']));

            $view->with('instansi', $instansi);
            $view->with('instansiLogoUrl', $instansi['logo']
                ? Storage::disk('public')->url($instansi['logo'])
                : null);

            $appearance = array_merge(Settings::defaults()['tampilan'], $settings->get('tampilan', Settings::defaults()['tampilan']));

            // Preferensi tampilan per-user (pengaturan-saya → Tampilan) mengalahkan pengaturan global.
            $user = auth()->user();
            if ($user) {
                $appearance['theme_color'] = $user->theme_color ?? $appearance['theme_color'];
                $appearance['mode'] = $user->mode ?? $appearance['mode'];
            }

            $view->with('appearance', $appearance);
        });
    }
}
