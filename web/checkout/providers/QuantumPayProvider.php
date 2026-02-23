<?php
/**
 * Quantum Pay Provider
 * Auth: X-Public-Key + X-Secret-Key headers (similar ao padrão brasileiro)
 * Base URL: https://api.quantumpay.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class QuantumPayProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.quantumpay.com.br';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $publicKey = $this->credentials['api_key'] ?? '';
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['client_secret'] ?? '';

        if (empty($secretKey)) {
            throw new Exception('Quantum Pay: Secret Key é obrigatória.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Public-Key: ' . $publicKey,
            'X-Secret-Key: ' . $secretKey,
        ];

        $body = [
            'amount' => (int)round($amount * 100),
            'external_reference' => $externalRef,
            'payer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/charge', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("Quantum Pay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrCodeBase64'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['copy_paste'] ?? $data['emv'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transactionId'] ?? uniqid('quantum_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $publicKey = $this->credentials['api_key'] ?? '';
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['client_secret'] ?? '';

        $headers = [
            'Accept: application/json',
            'X-Public-Key: ' . $publicKey,
            'X-Secret-Key: ' . $secretKey,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/charge/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200)
            return 'pending';

        $status = strtolower($result['body']['status'] ?? '');
        if (in_array($status, ['paid', 'completed', 'approved', 'settled']))
            return 'paid';
        if (in_array($status, ['expired', 'cancelled', 'canceled', 'failed']))
            return 'expired';

        return 'pending';
    }
}
