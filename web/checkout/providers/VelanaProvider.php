<?php
/**
 * Velana Provider
 * Docs: https://velana.readme.io
 * Auth: Basic Auth (secret_key como senha, usuário vazio ou igual à chave)
 *       Authorization: Basic base64(":{secret_key}")
 * Base URL: https://api.velana.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class VelanaProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.velana.com.br';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($secretKey)) {
            throw new Exception('Velana: Secret Key é obrigatória.');
        }

        // Velana usa Basic Auth: username vazio, password = secret_key
        $basicAuth = base64_encode(':' . $secretKey);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $basicAuth,
        ];

        $body = [
            'amount' => round($amount, 2),
            'external_id' => $externalRef,
            'payer' => [
                'name' => $name,
                'document' => preg_replace('/\D/', '', $document),
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/v1/pix/charge', 'POST', $headers, $body);

        if (!in_array($result['httpCode'], [200, 201])) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("Velana erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrCodeBase64'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['copy_paste'] ?? $data['emv'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['charge_id'] ?? uniqid('velana_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $secretKey = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';
        $basicAuth = base64_encode(':' . $secretKey);

        $headers = [
            'Accept: application/json',
            'Authorization: Basic ' . $basicAuth,
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
