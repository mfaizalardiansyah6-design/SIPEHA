<?php

namespace App\Http\Middleware;

use App\Services\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApplyAppSettings
{
    /**
     * Terapkan pengaturan runtime (bahasa, zona waktu, sesi, nama aplikasi)
     * dari tabel settings untuk setiap request HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Schema::hasTable('settings')) {
            $settings = app(Settings::class);

            $tampilan = array_merge(Settings::defaults()['tampilan'], $settings->get('tampilan', Settings::defaults()['tampilan']));
            $sistem = array_merge(Settings::defaults()['sistem'], $settings->get('sistem', Settings::defaults()['sistem']));
            $instansi = array_merge(Settings::defaults()['instansi'], $settings->get('instansi', Settings::defaults()['instansi']));

            app()->setLocale($tampilan['bahasa']);
            Carbon::setLocale($tampilan['bahasa']);
            config(['app.locale' => $tampilan['bahasa']]);

            date_default_timezone_set($tampilan['zona_waktu']);
            config(['app.timezone' => $tampilan['zona_waktu']]);

            if ($sistem['auto_lock']) {
                config(['session.lifetime' => (int) $sistem['session_timeout']]);
            } else {
                config(['session.lifetime' => 43200]);
            }

            if (! empty($instansi['nama'])) {
                config(['app.name' => $instansi['nama']]);
            }
        }

        return $next($request);
    }
}
