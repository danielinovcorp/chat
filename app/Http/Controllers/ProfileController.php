<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
	/**
	 * Display the user's profile form.
	 */
	public function edit(Request $request): View
	{
		return view('profile.edit', [
			'user' => $request->user(),
		]);
	}

	public function update(ProfileUpdateRequest $request): RedirectResponse
	{
		$user = $request->user();

		// Atualiza apenas os campos de texto
		$user->fill($request->only(['name', 'email']));

		if ($user->isDirty('email')) {
			$user->email_verified_at = null;
		}

		$user->save();

		// Remover foto, se solicitado
		if ($request->boolean('remove_avatar') && $user->avatar_path) {
			Storage::disk('public')->delete($user->avatar_path);
			$user->avatar_path = null;
			$user->save();
		}

		// Subir nova foto, se enviada
		if ($request->hasFile('avatar')) {
			// apaga a antiga, se houver
			if ($user->avatar_path) {
				Storage::disk('public')->delete($user->avatar_path);
			}

			$path = $request->file('avatar')->store('avatars', 'public'); // storage/app/public/avatars/xxx.jpg
			$user->avatar_path = $path;
			$user->save();
		}

		return Redirect::route('profile.edit')->with('status', 'profile-updated');
	}


	/**
	 * Delete the user's account.
	 */
	public function destroy(Request $request): RedirectResponse
	{
		$request->validateWithBag('userDeletion', [
			'password' => ['required', 'current_password'],
		]);

		$user = $request->user();

		Auth::logout();

		$user->delete();

		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return Redirect::to('/');
	}
}
