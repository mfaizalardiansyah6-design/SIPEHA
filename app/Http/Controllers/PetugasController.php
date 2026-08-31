<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StorePetugasRequest;
use App\Http\Requests\UpdatePetugasRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PetugasController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $role = $request->string('role')->value();

        $petugas = User::query()
            ->when($role === UserRole::Admin->value || $role === UserRole::User->value, fn ($query) => $query->where('role', $role))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('petugas.index', compact('petugas', 'search', 'role'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('petugas.form', ['petugas' => null]);
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $petugas = User::create([
            ...$request->validated(),
            'role' => UserRole::from($request->validated('role')),
        ]);

        app(AuditLogger::class)->log('create', 'users', $petugas->id, null, [
            'nama' => $petugas->name,
            'nip' => $petugas->nip,
            'role' => $petugas->role->label(),
        ]);

        return redirect()->route('petugas.index')->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('petugas.form', ['petugas' => $user]);
    }

    public function update(UpdatePetugasRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['role'] = UserRole::from($data['role']);

        $user->update($data);

        app(AuditLogger::class)->log('update', 'users', $user->id, null, [
            'nama' => $user->name,
            'nip' => $user->nip,
            'role' => $user->role->label(),
        ]);

        return redirect()->route('petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        app(AuditLogger::class)->log('delete', 'users', $user->id);

        return redirect()->route('petugas.index')->with('success', 'Petugas berhasil dihapus.');
    }
}
