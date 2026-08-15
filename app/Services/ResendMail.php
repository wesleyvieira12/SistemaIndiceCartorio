<?php

namespace App\Services;

use RuntimeException;

/**
 * Cliente HTTP da Resend (API).
 *
 * O SDK oficial (resend/resend-php) exige PHP 8.1+; este projeto usa PHP 7.4,
 * então a chamada é feita com cURL na API REST (sem Guzzle).
 *
 * Uso:
 *   $resend = ResendMail::client(config('services.resend.key'));
 *   $resend->emails->send([...]);
 */
class ResendMail
{
    /** @var string */
    protected $apiKey;

    /** @var object */
    public $emails;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey !== null ? $apiKey : config('services.resend.key');

        $self = $this;
        $this->emails = new class($self) {
            /** @var ResendMail */
            private $resend;

            public function __construct(ResendMail $resend)
            {
                $this->resend = $resend;
            }

            /**
             * @param  array  $payload
             * @return array
             */
            public function send(array $payload)
            {
                return $this->resend->sendEmail($payload);
            }
        };
    }

    /**
     * @param  string|null  $apiKey
     * @return static
     */
    public static function client($apiKey = null)
    {
        return new static($apiKey);
    }

    /**
     * @param  array  $payload
     * @return array
     */
    public function sendEmail(array $payload)
    {
        $key = (string) $this->apiKey;

        if ($key === '' || $key === 're_xxxxxxxxx') {
            throw new RuntimeException(
                'Defina RESEND_API_KEY no .env com sua chave real da Resend (substitua re_xxxxxxxxx).'
            );
        }

        if (! function_exists('curl_init')) {
            throw new RuntimeException('Extensão cURL do PHP não está disponível.');
        }

        $json = json_encode($payload);
        if ($json === false) {
            throw new RuntimeException('Não foi possível montar o JSON do e-mail.');
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$key,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => 20,
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new RuntimeException('Erro cURL ao chamar Resend: '.$error);
        }

        $decoded = json_decode((string) $body, true);
        if (! is_array($decoded)) {
            $decoded = ['raw' => $body];
        }

        if ($status < 200 || $status >= 300) {
            $message = isset($decoded['message']) ? $decoded['message'] : (string) $body;
            throw new RuntimeException('Resend HTTP '.$status.': '.$message);
        }

        return $decoded;
    }
}
