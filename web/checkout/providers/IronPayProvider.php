<?php
/**
 * IronPay Provider
 * Docs: https://ironpayapp.com.br
 * Auth: Authorization: Bearer {api_token}
 * Base URL: https://api.ironpayapp.com.br/api/public/v1
 */

require_once __DIR__ . '/BaseProvider.php';

class IronPayProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.ironpayapp.com.br/api/public/v1';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $apiToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($apiToken)) {
            throw new Exception('IronPay: Token da API é obrigatório.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiToken,
        ];

        $body = [
            'amount' => round($amount, 2),
            'description' => 'Pagamento via Pix',
            'externalRef' => $externalRef,
            'payer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/pix/charge', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("IronPay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrCodeBase64'] ?? $data['qrcode_image'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['copy_paste'] ?? $data['emv'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transactionId'] ?? uniqid('iron_')),
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

        $result = $this->makeRequest(self::BASE_URL . '/pix/charge/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200)
            return 'pending';

        $status = strtolower($result['body']['status'] ?? '');
        if (in_array($status, ['paid', 'completed', 'approved', 'settled', 'confirmed']))
            return 'paid';
        if (in_array($status, ['expired', 'cancelled', 'canceled', 'failed']))
            return 'expired';

        return 'pending';
    }
}
