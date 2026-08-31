<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const TTL = 300;

    /**
     * Nilai default untuk seluruh kelompok pengaturan.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            'instansi' => [
                'nama' => 'Lembaga Pemasyarakatan',
                'kode' => 'LP-00',
                'alamat' => '',
                'telepon' => '',
                'email' => '',
                'logo' => null,
            ],
            'tampilan' => [
                'theme_color' => 'blue',
                'mode' => 'light',
                'bahasa' => 'id',
                'zona_waktu' => 'Asia/Jakarta',
            ],
            'sistem' => [
                'audit_log' => true,
                'auto_lock' => true,
                'session_timeout' => 15,
                'login_attempts' => 5,
            ],
            'dokumen' => [
                'max_upload_mb' => 10,
                'allowed_types' => ['pdf', 'png', 'jpg', 'doc'],
                'storage_location' => 'local',
            ],
            'backup' => [
                'jadwal' => 'daily',
                'waktu' => '02:00',
                'api_url' => null,
                'api_key' => null,
            ],
        ];
    }

    /**
     * Ambil satu nilai pengaturan.
     *
     * Kelompok pengaturan dikembalikan dengan nilai default yang tergabung,
     * sehingga baris parsial (mis. hanya satu kunci) tetap aman dibaca.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            'setting.'.$key,
            self::TTL,
            fn () => Setting::where('key', $key)->value('value'),
        );

        if ($value === null) {
            return $default ?? self::defaults()[$key] ?? null;
        }

        if (is_array($value) && array_key_exists($key, self::defaults())) {
            return array_merge(self::defaults()[$key], $value);
        }

        return $value;
    }

    /**
     * Ambil seluruh kelompok pengaturan dengan nilai default.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $all = [];
        foreach (array_keys(self::defaults()) as $group) {
            $all[$group] = $this->get($group, self::defaults()[$group]);
        }

        return $all;
    }

    /**
     * Simpan satu kelompok pengaturan dan invalidasi cache.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget('setting.'.$key);
    }

    /**
     * Kembalikan pengaturan ke nilai default.
     */
    public function reset(): void
    {
        foreach (self::defaults() as $key => $value) {
            $this->set($key, $value);
        }
    }
}
