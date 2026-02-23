<?php

require_once __DIR__ . '/BaseProvider.php';

class PushinPayProvider extends BaseProvider
{

    private const BASE_URL = 'https://api.pushinpay.com.br/api/pix';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        // PushinPay só precisa do api_token (Bearer)
        $apiToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($apiToken)) {
            throw new Exception('Pushin Pay: api_token não configurado. Configure nas credenciais do provedor.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiToken,
        ];

        // Amount must be in cents (integer)
        $amountCents = (int)round($amount * 100);

        $body = [
            'value' => $amountCents,
            'webhook_url' => '',
            'split_rules' => [],
        ];

        // Endpoint correto: cashIn (camelCase)
        $result = $this->makeRequest(self::BASE_URL . '/cashIn', 'POST', $headers, $body);

        if ($result['httpCode'] !== 200 && $result['httpCode'] !== 201) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("Pushin Pay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        // Handle qrcode image (may already have data: prefix or not)
        $qrcodeImage = $data['qr_code_base64'] ?? $data['qrcode_image'] ?? null;
        if (!empty($qrcodeImage) && strpos($qrcodeImage, 'data:') === false) {
            $qrcodeImage = 'data:image/png;base64,' . $qrcodeImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? ($data['qrcode'] ?? ''),
            'qrcodeImage' => $qrcodeImage,
            'transactionId' => (string)($data['id'] ?? $data['transaction_id'] ?? uniqid('pp_')),
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

        // Endpoint correto: cashIn (camelCase)
        $result = $this->makeRequest(self::BASE_URL . '/cashIn/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200) {
            return 'pending';
        }

        $status = strtolower($result['body']['status'] ?? '');

        if (in_array($status, ['paid', 'paid_out', 'completed', 'approved'])) {
            return 'paid';
        }
        if (in_array($status, ['expired', 'cancelled', 'canceled', 'rejected'])) {
            return 'expired';
        }

        return 'pending';
    }
}
