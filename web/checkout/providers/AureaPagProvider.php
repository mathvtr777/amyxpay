<?php
/**
 * ÁureaPag Provider (PixToPay API)
 * Auth: Authorization: Bearer {api_token}
 * Base URL: https://api.aureapag.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class AureaPagProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.aureapag.com.br';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $apiToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($apiToken)) {
            throw new Exception('ÁureaPag: Token da API é obrigatório.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiToken,
        ];

        $body = [
            'amount' => (int)round($amount * 100),
            'externalId' => $externalRef,
            'customer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/cashin', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("ÁureaPag erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrCodeBase64'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['emv'] ?? $data['pixCopiaECola'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transactionId'] ?? uniqid('aurea_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $apiToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $apiToken,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/cashin/' . $transactionId, 'GET', $headers);

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
