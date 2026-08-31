<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Vérifie que l'utilisateur est :
     * 1. Authentifié
     * 2. Actif (is_active = true)
     * 3. Admin (role = admin)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            // Redirection vers login avec message
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = auth()->user();

        // 2. Vérifier si l'utilisateur est actif
        if (!$user->isActive()) {
            // Déconnecter l'utilisateur inactif
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.');
        }

        // 3. Vérifier si l'utilisateur est admin
        if (!$user->isAdmin()) {
            // 403 Forbidden pour les utilisateurs non-admin
            abort(403, 'Accès refusé. Cette page est réservée aux administrateurs.');
        }

        // Tout est OK, continuer
        return $next($request);
    }
}
