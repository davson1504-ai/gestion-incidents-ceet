<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $dbVersion = 'N/A';

        try {
            $result = DB::selectOne('select version() as version');
            $dbVersion = (string) ($result->version ?? 'N/A');
        } catch (Throwable) {
            $dbVersion = 'N/A';
        }

        $recentActivities = collect();

        try {
            if (Schema::hasTable('logs')) {
                $recentActivities = Log::query()
                    ->where('user_id', $request->user()->id)
                    ->orWhere(function ($query) use ($request): void {
                        $query
                            ->where('target_type', $request->user()::class)
                            ->where('target_id', $request->user()->id);
                    })
                    ->latest('created_at')
                    ->limit(5)
                    ->get();
            }
        } catch (Throwable) {
            $recentActivities = collect();
        }

        return view('profile.edit', [
            'user' => $request->user()->loadMissing(['roles', 'departement']),
            'dbVersion' => $dbVersion,
            'recentActivities' => $recentActivities,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        try {
            if (Schema::hasTable('logs')) {
                Log::query()->create([
                    'user_id' => $user->id,
                    'action' => 'update_profile',
                    'module' => 'users',
                    'target_type' => $user::class,
                    'target_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'details' => [
                        'message' => 'Mise à jour du profil personnel',
                    ],
                ]);
            }
        } catch (Throwable) {
            // Le profil reste mis à jour même si la journalisation échoue.
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

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