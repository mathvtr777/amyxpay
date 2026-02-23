<?php
/**
 * Pronttus Provider
 * Docs: https://pronttus.readme.io/reference
 * Auth: x-client-id (public key) + x-client-secret (private key) headers
 * Base URL: https://api.pronttus.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class PronttusProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.pronttus.com.br';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $clientId = $this->credentials['client_id'] ?? $this->credentials['api_key'] ?? '';
        $clientSecret = $this->credentials['client_secret'] ?? $this->credentials['api_token'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception('Pronttus: Client ID e Client Secret são obrigatórios.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'x-client-id: ' . $clientId,
            'x-client-secret: ' . $clientSecret,
        ];

        $body = [
            'method' => 'pix',
            'amount' => (int)round($amount * 100),
            'external_reference' => $externalRef,
            'payer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/payment', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("Pronttus erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrCodeBase64'] ?? $data['pixQrCodeBase64'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['pixCopiaECola'] ?? $data['copy_paste'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transactionId'] ?? uniqid('pronttus_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $clientId = $this->credentials['client_id'] ?? $this->credentials['api_key'] ?? '';
        $clientSecret = $this->credentials['client_secret'] ?? $this->credentials['api_token'] ?? '';

        $headers = [
            'Accept: application/json',
            'x-client-id: ' . $clientId,
            'x-client-secret: ' . $clientSecret,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/payment/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200)
            return 'pending';

        $status = strtolower($result['body']['status'] ?? '');
        if (in_array($status, ['paid', 'completed', 'approved', 'settled', 'captured']))
            return 'paid';
        if (in_array($status, ['expired', 'cancelled', 'canceled', 'failed', 'rejected']))
            return 'expired';

        return 'pending';
    }
}
