<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Services\ProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function edit(): Response
    {
        return Inertia::render('Profile/Edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $this->profile->updateProfile($user, $data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        if (! $this->profile->updatePassword($request->user(), $data['current_password'], $data['password'])) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function photo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $this->profile->updatePhoto($request->user(), $data['photo']);

        return back()->with('success', 'Foto profil diperbarui.');
    }
}
