<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    protected $table = 'scheduled_emails';

    protected $fillable = [
        'subject',
        'body',
        'recipients',
        'scheduled_at',
        'repeat_interval',
        'status',
        'sent_at',
        'last_sent_at',
        'error_message',
        'created_by',
    ];

    protected $dates = [
        'scheduled_at',
        'sent_at',
        'last_sent_at',
        'created_at',
        'updated_at',
    ];

    public static function repeatOptions()
    {
        return [
            'none' => 'Não repetir',
            'day' => 'Todos os dias',
            'month' => 'Todo mês',
            'year' => 'Todo ano',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array
     */
    public function recipientList()
    {
        $raw = (string) $this->recipients;
        // Aceita e-mails separados por vírgula, ponto-e-vírgula ou quebra de linha
        $raw = str_replace(["\r\n", "\r", "\n", ';'], ',', $raw);
        $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $emails = [];

        foreach ($parts as $part) {
            $email = strtolower(trim($part));
            // Remove caracteres invisíveis comuns de copy/paste
            $email = preg_replace('/[^\x20-\x7E]/', '', $email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $email;
            }
        }

        return array_values($emails);
    }

    public function statusLabel()
    {
        $labels = [
            'pending' => 'Agendado',
            'sent' => 'Enviado',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
        ];

        return isset($labels[$this->status]) ? $labels[$this->status] : $this->status;
    }

    public function repeatLabel()
    {
        $options = self::repeatOptions();

        return isset($options[$this->repeat_interval])
            ? $options[$this->repeat_interval]
            : 'Não repetir';
    }

    public function isRecurring()
    {
        return in_array($this->repeat_interval, ['day', 'month', 'year'], true);
    }

    /**
     * Próxima data/hora de envio a partir da data atual do agendamento.
     *
     * @param  \Carbon\Carbon|null  $from
     * @return \Carbon\Carbon
     */
    public function nextScheduledAt($from = null)
    {
        $base = $from ? $from->copy() : Carbon::parse($this->scheduled_at);

        switch ($this->repeat_interval) {
            case 'day':
                return $base->addDay();
            case 'month':
                return $base->addMonth();
            case 'year':
                return $base->addYear();
            default:
                return $base;
        }
    }

    public function isEditable()
    {
        return $this->status === 'pending';
    }
}
