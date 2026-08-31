<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class PasswordResetRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $expiryMinutes = 30;
        $timeLimit = now()->addMinutes($expiryMinutes);

        $token = \Illuminate\Support\Str::random(64);

        $cacheKey = 'password-setup-' . $token;
        Cache::put($cacheKey, $notifiable->id, $timeLimit);

        $resetLink = route('password.setup', ['token' => $token]);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - PMO GUT')
            ->text('emails.password-reset', [
                'userName' => $notifiable->name,
                'resetLink' => $resetLink,
                'expiryMinutes' => $expiryMinutes,
            ]);
    }
}
