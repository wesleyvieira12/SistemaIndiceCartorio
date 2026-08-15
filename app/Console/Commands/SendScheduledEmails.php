<?php

namespace App\Console\Commands;

use App\Models\ScheduledEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmails extends Command
{
    protected $signature = 'emails:send-scheduled';

    protected $description = 'Envia e-mails agendados cuja data/hora já chegou';

    public function handle()
    {
        $now = Carbon::now();

        $due = ScheduledEmail::where('status', 'pending')
            ->where('scheduled_at', '<=', $now->format('Y-m-d H:i:s'))
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();

        if ($due->isEmpty()) {
            $this->info('Nenhum e-mail pendente para enviar.');

            return 0;
        }

        foreach ($due as $email) {
            $recipients = $email->recipientList();

            if (count($recipients) === 0) {
                $email->status = 'failed';
                $email->error_message = 'Nenhum destinatário válido.';
                $email->save();
                $this->error('#'.$email->id.' falhou: sem destinatários');
                continue;
            }

            try {
                $subject = $email->subject;
                $body = $email->body;

                foreach ($recipients as $to) {
                    Mail::send('emails.scheduled', [
                        'body' => $body,
                        'subject' => $subject,
                    ], function ($message) use ($to, $subject) {
                        $message->to($to)->subject($subject);
                    });
                }

                $email->status = 'sent';
                $email->sent_at = Carbon::now();
                $email->error_message = null;
                $email->save();

                $this->info('#'.$email->id.' enviado para '.count($recipients).' destinatário(s)');
            } catch (\Throwable $e) {
                $email->status = 'failed';
                $email->error_message = substr($e->getMessage(), 0, 1000);
                $email->save();

                $this->error('#'.$email->id.' falhou: '.$e->getMessage());
            }
        }

        return 0;
    }
}
