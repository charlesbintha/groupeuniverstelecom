<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class UserAccountCreated extends Notification implements ShouldQueue
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

        $setupLink = route('password.setup', ['token' => $token]);

        return (new MailMessage)
            ->subject('Bienvenue sur PMO Groupe Univers Telecom')
            ->text('emails.user-account-created', [
                'userName' => $notifiable->name,
                'userEmail' => $notifiable->email,
                'setupLink' => $setupLink,
                'expiryMinutes' => $expiryMinutes,
            ]);
    }
}
