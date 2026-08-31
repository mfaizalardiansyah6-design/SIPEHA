<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pengaturan-saya.index', [
            'user' => $request->user(),
        ]);
    }

    public function updateAppearance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_color' => ['required', Rule::in(['blue', 'teal', 'indigo', 'slate'])],
            'mode' => ['required', Rule::in(['light', 'dark'])],
        ]);

        $user = $request->user();
        $user->fill($validated)->save();

        app(AuditLogger::class)->log('update', 'users', $user->id, null, $validated);

        return back()->with('success', 'Pengaturan tampilan berhasil disimpan.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', Rule::unique(User::class)->ignore($request->user()->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
            'jabatan' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->fill($validated)->save();

        app(AuditLogger::class)->log('update', 'users', $user->id, null, $validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $validated['photo']->store('uploads/profile-photos', 'public');

        $user->update(['profile_photo_path' => $path]);

        app(AuditLogger::class)->log('update', 'users', $user->id, null, ['profile_photo_path' => $path]);

        return back()->with('success', 'Foto profil berhasil diunggah.');
    }
}
