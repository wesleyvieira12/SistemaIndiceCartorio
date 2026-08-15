<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * @var string
     */
    public $token;

    /**
     * @param  string  $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = method_exists($notifiable, 'getEmailForPasswordReset')
            ? $notifiable->getEmailForPasswordReset()
            : $notifiable->email;

        $resetUrl = url(route('password.reset', $this->token, false));
        if (! empty($email)) {
            $resetUrl .= (strpos($resetUrl, '?') === false ? '?' : '&').http_build_query([
                'email' => $email,
            ]);
        }

        return (new MailMessage)
            ->subject('Recuperação de senha — 1ª Serventia Extrajudicial de Oeiras')
            ->greeting('Olá!')
            ->line('Você recebeu este e-mail porque foi solicitada a redefinição de senha da sua conta no Sistema de Índices.')
            ->action('Redefinir senha', $resetUrl)
            ->line('Este link expira em '.config('auth.passwords.users.expire', 60).' minutos.')
            ->line('Se você não solicitou a redefinição, ignore este e-mail — nenhuma alteração será feita.')
            ->salutation('Atenciosamente, 1ª Serventia Extrajudicial de Oeiras – PI');
    }
}
