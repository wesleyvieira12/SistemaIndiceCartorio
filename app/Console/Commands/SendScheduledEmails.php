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

                $email->sent_at = Carbon::now();
                $email->last_sent_at = Carbon::now();
                $email->error_message = null;

                if ($email->isRecurring()) {
                    // Mantém pendente e agenda a próxima ocorrência
                    $next = $email->nextScheduledAt(Carbon::parse($email->scheduled_at));
                    // Se ainda ficou no passado (servidor parado), avança até o futuro
                    while ($next->lte(Carbon::now())) {
                        $next = $email->nextScheduledAt($next);
                    }
                    $email->scheduled_at = $next;
                    $email->status = 'pending';
                    $this->info('#'.$email->id.' enviado (repete '.$email->repeat_interval.'); próximo: '.$next->format('Y-m-d H:i'));
                } else {
                    $email->status = 'sent';
                    $this->info('#'.$email->id.' enviado para '.count($recipients).' destinatário(s)');
                }

                $email->save();
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
