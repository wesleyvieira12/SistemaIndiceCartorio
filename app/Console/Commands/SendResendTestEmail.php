<?php

namespace App\Console\Commands;

use App\Services\ResendMail;
use Illuminate\Console\Command;

class SendResendTestEmail extends Command
{
    protected $signature = 'resend:test
                            {--to=moderador2012.2@gmail.com : Destinatário}
                            {--from=onboarding@resend.dev : Remetente (domínio verificado na Resend)}';

    protected $description = 'Envia um e-mail de teste via API da Resend';

    public function handle()
    {
        $to = $this->option('to');
        $from = $this->option('from');

        $this->info('Enviando e-mail de teste via Resend...');

        try {
            $resend = ResendMail::client(config('services.resend.key'));

            $result = $resend->emails->send([
                'from' => $from,
                'to' => $to,
                'subject' => 'Hello World',
                'html' => '<p>Congrats on sending your <strong>first email</strong>!</p>',
            ]);
        } catch (\Exception $e) {
            $this->error('Falha ao enviar: '.$e->getMessage());

            return 1;
        }

        $this->info('E-mail enviado.');
        if (! empty($result['id'])) {
            $this->line('ID Resend: '.$result['id']);
        }

        return 0;
    }
}
