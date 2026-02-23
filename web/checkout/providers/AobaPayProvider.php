<?php
/**
 * AobaPay Provider
 * Docs: https://docs.aobapay.com/api-reference/criar-qrcode-pix
 * Auth: Authorization: Bearer {secret_key}
 * Base URL: https://api.aobapay.com
 */

require_once __DIR__ . '/BaseProvider.php';

class AobaPayProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.aobapay.com';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($secretKey)) {
            throw new Exception('AobaPay: Secret Key é obrigatória.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $secretKey,
        ];

        $body = [
            'amount' => (int)round($amount * 100), // centavos
            'externalID' => $externalRef,
            'customer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/charge/pix/create', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("AobaPay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qrCodeImage'] ?? $data['qr_code_image'] ?? $data['qrcode_image'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['pixCopiaECola'] ?? $data['qr_code'] ?? $data['code'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['chargeID'] ?? uniqid('aoba_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $secretKey,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/charge/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200)
            return 'pending';

        $status = strtolower($result['body']['status'] ?? '');
        if (in_array($status, ['paid', 'completed', 'approved', 'settled']))
            return 'paid';
        if (in_array($status, ['expired', 'cancelled', 'canceled', 'rejected', 'failed']))
            return 'expired';

        return 'pending';
    }
}
