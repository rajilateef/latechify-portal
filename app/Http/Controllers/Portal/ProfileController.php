<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('portal.profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:40',
            'bio'    => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }
        unset($data['avatar']);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('password_success', 'Password changed successfully.');
    }
}
