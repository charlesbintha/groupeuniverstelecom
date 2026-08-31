<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        // Redirect if already logged in
        if (Auth::check()) {
            return redirect()->route('projects.index');
        }

        // Get remembered email from cookie
        $rememberEmail = Cookie::get('remember_email', '');
        $rememberChecked = ! empty($rememberEmail);

        return view('auth.login', compact('rememberEmail', 'rememberChecked'));
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password');
        $remember = $request->boolean('remember_me');

        // Find user with employee relation
        $user = User::with('employee')
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        // Verify password
        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('email', 'remember_me'))
                ->withErrors([
                    'email' => 'Identifiants invalides.',
                ]);
        }

        // Log the user in
        Auth::login($user, $remember);

        // Regenerate session to prevent fixation attacks
        $request->session()->regenerate();

        // Handle "Remember Me" - store email in cookie for 180 days
        if ($remember) {
            Cookie::queue('remember_email', $email, 60 * 24 * 180); // 180 days
        } else {
            Cookie::queue(Cookie::forget('remember_email'));
        }

        // Redirect to intended page or projects list
        $redirectTo = $request->input('redirect', route('projects.index'));

        // Avoid redirecting back to login
        if (str_contains($redirectTo, 'login')) {
            $redirectTo = route('projects.index');
        }

        return redirect()->intended($redirectTo);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        try {
            if ($user && Schema::hasTable('activity_logs')) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name ?: $user->employee?->prenom_nom,
                    'user_email' => $user->email,
                    'action' => 'Déconnexion',
                    'route_name' => 'logout',
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => 302,
                ]);
            }
        } catch (\Throwable) {
            // La déconnexion ne doit jamais être bloquée par la journalisation.
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
