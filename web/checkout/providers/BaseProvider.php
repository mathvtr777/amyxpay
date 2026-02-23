<?php

abstract class BaseProvider
{
    protected $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Create a Pix payment and return QR code data.
     *
     * @param float  $amount   Amount in BRL (e.g. 10.00)
     * @param string $name     Customer name
     * @param string $document Customer CPF (digits only)
     * @param string $externalRef Unique reference for this transaction
     * @return array ['paymentCode' => string, 'qrcodeImage' => string|null, 'transactionId' => string, 'expiresIn' => int]
     */
    abstract public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array;

    /**
     * Check payment status by provider transaction ID.
     *
     * @param string $transactionId
     * @return string  'paid' | 'pending' | 'expired' | 'cancelled'
     */
    abstract public function checkPaymentStatus(string $transactionId): string;

    /**
     * Helper: make an HTTP request using cURL.
     */
    protected function makeRequest(string $url, string $method, array $headers, $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
            }
        }
        elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: $error");
        }

        $decoded = json_decode($response, true);
        return [
            'httpCode' => $httpCode,
            'body' => $decoded ?? $response,
            'raw' => $response,
        ];
    }
}
