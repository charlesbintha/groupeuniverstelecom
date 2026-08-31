<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordSetupController extends Controller
{
    public function show(Request $request, $token)
    {
        $cacheKey = 'password-setup-' . $token;

        if (!Cache::has($cacheKey)) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Ce lien a expiré ou a déjà été utilisé. Veuillez contacter l\'administrateur pour obtenir un nouveau lien.']);
        }

        $userId = Cache::get($cacheKey);
        $user = User::findOrFail($userId);

        return view('auth.setup-password', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function store(Request $request, $token)
    {
        $cacheKey = 'password-setup-' . $token;

        if (!Cache::has($cacheKey)) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Ce lien a expiré ou a déjà été utilisé. Veuillez contacter l\'administrateur pour obtenir un nouveau lien.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userId = Cache::get($cacheKey);
        $user = User::findOrFail($userId);
        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget($cacheKey);

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été créé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
