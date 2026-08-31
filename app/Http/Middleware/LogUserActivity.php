<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();

        if (! $user || $request->routeIs('admin.activity.export')) {
            return $response;
        }

        try {
            if (! Schema::hasTable('activity_logs')) {
                return $response;
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name ?: $user->employee?->prenom_nom,
                'user_email' => $user->email,
                'action' => $this->actionLabel($request),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Impossible d’enregistrer l’activité utilisateur.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    private function actionLabel(Request $request): string
    {
        $routeName = $request->route()?->getName();

        return match ($routeName) {
            'login.attempt' => 'Connexion',
            'dashboard' => 'Consultation du tableau de bord',
            'projects.index' => 'Consultation des projets',
            'projects.show' => 'Consultation d’un projet',
            'projects.create' => 'Ouverture du formulaire projet',
            'projects.store' => 'Création d’un projet',
            'projects.edit' => 'Ouverture de la modification d’un projet',
            'projects.update' => 'Modification d’un projet',
            'projects.destroy' => 'Suppression d’un projet',
            'projects.duplicate' => 'Duplication d’un projet',
            'projects.documents.download' => 'Téléchargement d’un document',
            'projects.documents.delete' => 'Suppression d’un document',
            'projects.report' => 'Consultation d’un rapport projet',
            'admin.users.index' => 'Consultation des utilisateurs',
            'admin.users.create' => 'Ouverture du formulaire utilisateur',
            'admin.users.store' => 'Création d’un utilisateur',
            'admin.users.edit' => 'Ouverture de la modification d’un utilisateur',
            'admin.users.update' => 'Modification d’un utilisateur',
            'admin.users.toggle' => 'Changement du statut d’un utilisateur',
            'admin.users.resetPassword' => 'Réinitialisation d’un mot de passe',
            'admin.users.destroy' => 'Suppression d’un utilisateur',
            'admin.activity.index' => 'Consultation du journal d’activité',
            default => $request->method().' '.($routeName ?: $request->path()),
        };
    }
}
