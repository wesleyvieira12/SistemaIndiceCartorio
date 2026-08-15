<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCodeNotification extends Notification
{
    /** @var string */
    public $code;

    /**
     * @param  string  $code
     */
    public function __construct($code)
    {
        $this->code = $code;
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
        $minutes = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Código de recuperação de senha — 1ª Serventia Extrajudicial de Oeiras')
            ->greeting('Olá!')
            ->line('Você solicitou a redefinição de senha no Sistema de Índices.')
            ->line('Use o código abaixo no site para continuar:')
            ->line($this->code)
            ->line('Este código expira em '.$minutes.' minutos.')
            ->line('Se você não solicitou a redefinição, ignore este e-mail.')
            ->salutation('Atenciosamente, 1ª Serventia Extrajudicial de Oeiras – PI');
    }
}
