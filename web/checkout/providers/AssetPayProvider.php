<?php
/**
 * AssetPay Provider
 * Auth: X-Company-ID + Authorization: Bearer {secret_key}
 * Base URL: https://api.assetpay.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class AssetPayProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.assetpay.com.br';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';
        $companyId = $this->credentials['client_id'] ?? $this->credentials['company_id'] ?? '';

        if (empty($secretKey)) {
            throw new Exception('AssetPay: Secret Key é obrigatória.');
        }
        if (empty($companyId)) {
            throw new Exception('AssetPay: Company ID é obrigatório.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $secretKey,
            'X-Company-ID: ' . $companyId,
        ];

        $body = [
            'amount' => (int)round($amount * 100),
            'external_reference' => $externalRef,
            'payer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/cashIn', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("AssetPay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrcode_image'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['copy_paste'] ?? $data['emv'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transactionId'] ?? uniqid('asset_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';
        $companyId = $this->credentials['client_id'] ?? $this->credentials['company_id'] ?? '';

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $secretKey,
            'X-Company-ID: ' . $companyId,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/cashIn/' . $transactionId, 'GET', $headers);

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
