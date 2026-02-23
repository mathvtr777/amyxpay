<?php
/**
 * Sync Pay Provider
 * Auth: OAuth2 client_credentials (client_id + client_secret → Bearer token)
 * Base URL: https://api.syncpay.com.br
 */

require_once __DIR__ . '/BaseProvider.php';

class SyncPayProvider extends BaseProvider
{
    private const BASE_URL = 'https://api.syncpay.com.br';

    private function getAccessToken(): string
    {
        $clientId = $this->credentials['client_id'] ?? '';
        $clientSecret = $this->credentials['client_secret'] ?? $this->credentials['api_token'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception('Sync Pay: Client ID e Client Secret são obrigatórios.');
        }

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ];

        $body = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        $ch = curl_init(self::BASE_URL . '/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            throw new Exception("Sync Pay: Falha na autenticação (HTTP $code).");
        }

        $data = json_decode($resp, true);
        return $data['access_token'] ?? '';
    }

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $token = $this->getAccessToken();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
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
            throw new Exception("Sync Pay erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];

        $qrImage = $data['qr_code_base64'] ?? $data['qrcode_image'] ?? null;
        if (!empty($qrImage) && strpos($qrImage, 'data:') === false) {
            $qrImage = 'data:image/png;base64,' . $qrImage;
        }

        return [
            'paymentCode' => $data['qr_code'] ?? $data['copy_paste'] ?? $data['emv'] ?? '',
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? $data['transaction_id'] ?? uniqid('sync_')),
            'expiresIn' => 1800,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        try {
            $token = $this->getAccessToken();
        }
        catch (Exception $e) {
            return 'pending';
        }

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
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
