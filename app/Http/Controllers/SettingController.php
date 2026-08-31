<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = app(Settings::class)->all();
        $logoUrl = $settings['instansi']['logo']
            ? Storage::disk('public')->url($settings['instansi']['logo'])
            : null;

        return view('pengaturan.index', compact('settings', 'logoUrl'));
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $defaults = Settings::defaults();

        abort_unless(array_key_exists($group, $defaults), 404);

        $request->validate($this->rulesFor($group));

        $data = $request->except(['_token', '_method']);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:2048']]);
            $data['logo'] = $request->file('logo')->store('uploads/settings', 'public');
        }

        $data = array_intersect_key($data, $defaults[$group]);

        if ($group === 'backup' && empty($data['api_key'] ?? null)) {
            $current = app(Settings::class)->get('backup', $defaults['backup']);
            $data['api_key'] = $current['api_key'] ?? null;
        }

        app(Settings::class)->set($group, $data);

        app(AuditLogger::class)->log('update', 'settings', $group, null, $data);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rulesFor(string $group): array
    {
        return match ($group) {
            'sistem' => [
                'session_timeout' => ['required', 'integer', 'min:1', 'max:1440'],
                'login_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            ],
            'instansi' => [
                'nama' => ['nullable', 'string', 'max:255'],
                'kode' => ['nullable', 'string', 'max:50'],
                'alamat' => ['nullable', 'string', 'max:500'],
                'telepon' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
            default => [],
        };
    }

    public function reset(): RedirectResponse
    {
        app(Settings::class)->reset();

        app(AuditLogger::class)->log('reset', 'settings');

        return back()->with('success', 'Pengaturan dikembalikan ke nilai default.');
    }
}
