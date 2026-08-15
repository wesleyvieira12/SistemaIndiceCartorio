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
        $fromName = config('mail.from.name') ?: 'Sistema de Indices';

        if (! $fromAddress) {
            $this->error('MAIL_FROM_ADDRESS não está definido no .env');

            return 1;
        }

        $this->info('Enviando e-mail de teste via Mail do Laravel...');
        $this->line('Driver: '.config('mail.driver'));
        $this->line('Host: '.config('mail.host'));
        $this->line('Port: '.config('mail.port'));
        $this->line('Encryption: '.(config('mail.encryption') ?: '(none)'));
        $this->line('De: '.$fromAddress);
        $this->line('Para: '.$to);

        try {
            Mail::raw(
                'Congrats on sending your first email!',
                function ($message) use ($to, $fromAddress, $fromName) {
                    $message->to($to)
                        ->from($fromAddress, $fromName)
                        ->subject('Hello World');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Falha ao enviar: '.$e->getMessage());
            $this->line($e->getFile().':'.$e->getLine());

            return 1;
        }

        $this->info('E-mail enviado com sucesso.');

        return 0;
    }
}
