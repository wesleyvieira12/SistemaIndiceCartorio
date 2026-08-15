<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendResendTestEmail extends Command
{
    protected $signature = 'resend:test
                            {--to=moderador2012.2@gmail.com : Destinatário}
                            {--from= : Remetente (padrão: MAIL_FROM_ADDRESS do .env)}';

    protected $description = 'Envia e-mail de teste pelo Mail do Laravel (SMTP / Resend)';

    public function handle()
    {
        $to = $this->option('to');
        $fromAddress = $this->option('from') ?: config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->info('Enviando e-mail de teste via Mail do Laravel...');
        $this->line('Driver: '.config('mail.driver'));
        $this->line('Host: '.config('mail.host'));
        $this->line('De: '.$fromAddress);
        $this->line('Para: '.$to);

        try {
            Mail::send([], [], function ($message) use ($to, $fromAddress, $fromName) {
                $message->to($to)
                    ->from($fromAddress, $fromName)
                    ->subject('Hello World')
                    ->setBody(
                        '<p>Congrats on sending your <strong>first email</strong>!</p>',
                        'text/html'
                    );
            });
        } catch (\Exception $e) {
            $this->error('Falha ao enviar: '.$e->getMessage());

            return 1;
        }

        $this->info('E-mail enviado com sucesso.');

        return 0;
    }
}
