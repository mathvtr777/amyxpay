<?php

require_once __DIR__ . '/BaseProvider.php';

class MercadoPagoProvider extends BaseProvider
{

    private const BASE_URL = 'https://api.mercadopago.com/v1';

    public function createPixPayment(float $amount, string $name, string $document, string $externalRef): array
    {
        $accessToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        if (empty($accessToken)) {
            throw new Exception('Mercado Pago: access_token (api_token) é obrigatório.');
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . $externalRef,
        ];

        $body = [
            'transaction_amount' => $amount,
            'description' => 'Pagamento via Pix',
            'payment_method_id' => 'pix',
            'external_reference' => $externalRef,
            'payer' => [
                'email' => 'cliente@email.com',
                'first_name' => $name,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $document,
                ],
            ],
        ];

        $result = $this->makeRequest(self::BASE_URL . '/payments', 'POST', $headers, $body);

        if ($result['httpCode'] !== 200 && $result['httpCode'] !== 201) {
            $msg = is_array($result['body']) ? json_encode($result['body']) : $result['raw'];
            throw new Exception("Mercado Pago erro ({$result['httpCode']}): $msg");
        }

        $data = $result['body'];
        $pixData = $data['point_of_interaction']['transaction_data'] ?? [];
        $paymentCode = $pixData['qr_code'] ?? '';
        $qrImage = isset($pixData['qr_code_base64']) ? 'data:image/png;base64,' . $pixData['qr_code_base64'] : null;

        return [
            'paymentCode' => $paymentCode,
            'qrcodeImage' => $qrImage,
            'transactionId' => (string)($data['id'] ?? uniqid('mp_')),
            'expiresIn' => 3600,
        ];
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        $accessToken = $this->credentials['api_token'] ?? $this->credentials['api_key'] ?? '';

        $headers = [
            'Authorization: Bearer ' . $accessToken,
        ];

        $result = $this->makeRequest(self::BASE_URL . '/payments/' . $transactionId, 'GET', $headers);

        if ($result['httpCode'] !== 200) {
            return 'pending';
        }

        $status = strtolower($result['body']['status'] ?? '');

        if ($status === 'approved')
            return 'paid';
        if (in_array($status, ['cancelled', 'rejected', 'refunded', 'charged_back']))
            return 'expired';

        return 'pending';
    }
}
