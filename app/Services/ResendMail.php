<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

/**
 * Cliente HTTP da Resend (API).
 *
 * O SDK oficial (resend/resend-php) exige PHP 8.1+; este projeto usa PHP 7.4,
 * então a chamada é feita direto na API REST.
 *
 * Uso:
 *   $resend = ResendMail::client(config('services.resend.key'));
 *   $resend->emails->send([...]);
 */
class ResendMail
{
    /** @var string */
    protected $apiKey;

    /** @var Client */
    protected $http;

    /** @var object */
    public $emails;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey !== null ? $apiKey : config('services.resend.key');
        $this->http = new Client([
            'base_uri' => 'https://api.resend.com/',
            'timeout' => 20,
        ]);

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

        $response = $this->http->post('emails', [
            'headers' => [
                'Authorization' => 'Bearer '.$key,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'http_errors' => true,
        ]);

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : ['raw' => $body];
    }
}
