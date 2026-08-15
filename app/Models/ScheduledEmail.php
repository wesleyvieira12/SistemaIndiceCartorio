<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    protected $table = 'scheduled_emails';

    protected $fillable = [
        'subject',
        'body',
        'recipients',
        'scheduled_at',
        'status',
        'sent_at',
        'error_message',
        'created_by',
    ];

    protected $dates = [
        'scheduled_at',
        'sent_at',
        'created_at',
        'updated_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array
     */
    public function recipientList()
    {
        $raw = str_replace([';', "\r\n", "\r"], [',', ',', ','], (string) $this->recipients);
        $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $emails = [];

        foreach ($parts as $part) {
            $email = strtolower(trim($part));
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

    public function isEditable()
    {
        return $this->status === 'pending';
    }
}
