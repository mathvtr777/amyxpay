<?php

require_once __DIR__ . '/PushinPayProvider.php';
require_once __DIR__ . '/MercadoPagoProvider.php';
require_once __DIR__ . '/AobaPayProvider.php';
require_once __DIR__ . '/AtomoPayProvider.php';
require_once __DIR__ . '/AureaPagProvider.php';
require_once __DIR__ . '/SigiloPayProvider.php';
require_once __DIR__ . '/AssetPayProvider.php';
require_once __DIR__ . '/SyncPayProvider.php';
require_once __DIR__ . '/VelanaProvider.php';
require_once __DIR__ . '/QuantumPayProvider.php';
require_once __DIR__ . '/PronttusProvider.php';
require_once __DIR__ . '/IronPayProvider.php';

class ProviderFactory
{
    /**
     * Mapeamento de nomes de provedores → classes PHP
     */
    private static array $map = [
        // PushinPay
        'pushin pay' => 'PushinPayProvider',
        'pushinpay' => 'PushinPayProvider',
        // Mercado Pago
        'mercado pago' => 'MercadoPagoProvider',
        'mercadopago' => 'MercadoPagoProvider',
        // AobaPay
        'aoba pay' => 'AobaPayProvider',
        'aobapay' => 'AobaPayProvider',
        // Átomo Pay
        'átomo pay' => 'AtomoPayProvider',
        'atomo pay' => 'AtomoPayProvider',
        'atomopay' => 'AtomoPayProvider',
        // ÁureaPag
        'áureapag' => 'AureaPagProvider',
        'aureapag' => 'AureaPagProvider',
        'aurea pag' => 'AureaPagProvider',
        'àureapag' => 'AureaPagProvider',
        // SigiloPay
        'sigilo pay' => 'SigiloPayProvider',
        'sigilopay' => 'SigiloPayProvider',
        // AssetPay
        'asset pay' => 'AssetPayProvider',
        'assetpay' => 'AssetPayProvider',
        // Sync Pay
        'sync pay' => 'SyncPayProvider',
        'syncpay' => 'SyncPayProvider',
        // Velana
        'velana' => 'VelanaProvider',
        // Quantum Pay
        'quantum pay' => 'QuantumPayProvider',
        'quantumpay' => 'QuantumPayProvider',
        // Pronttus
        'pronttus' => 'PronttusProvider',
        // IronPay
        'iron pay' => 'IronPayProvider',
        'ironpay' => 'IronPayProvider',
    ];

    /**
     * Cria uma instância do provider.
     *
     * @param string $providerName  Nome do provedor (ex: 'Pushin Pay', 'Mercado Pago')
     * @param array  $credentials   Keys: api_key, api_token, client_id, client_secret
     * @return BaseProvider
     */
    public static function create(string $providerName, array $credentials): BaseProvider
    {
        $key = strtolower(trim($providerName));
        // remove acentos para comparação mais robusta
        $key = iconv('UTF-8', 'ASCII//TRANSLIT', $key);
        $key = preg_replace('/[^a-z0-9 ]/', '', $key);

        if (isset(self::$map[$key])) {
            $class = self::$map[$key];
            return new $class($credentials);
        }

        // Tenta por substring
        foreach (self::$map as $alias => $class) {
            if (strpos($key, $alias) !== false || strpos($alias, $key) !== false) {
                return new $class($credentials);
            }
        }

        throw new Exception("Provedor não suportado: $providerName");
    }

    /**
     * Lista todos os provedores disponíveis com seus campos obrigatórios.
     */
    public static function getAvailableProviders(): array
    {
        return [
            [
                'name' => 'Pushin Pay',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Token da API', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Mercado Pago',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Chave API (Access Token)', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Aoba Pay',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Atomo Pay',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Token da API', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Aurea Pag',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Token da API', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Sigilo Pay',
                'fields' => [
                    ['key' => 'api_key', 'label' => 'Public Key', 'type' => 'text', 'required' => true],
                    ['key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Asset Pay',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                    ['key' => 'client_id', 'label' => 'Company ID', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Sync Pay',
                'fields' => [
                    ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
                    ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Velana',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Quantum Pay',
                'fields' => [
                    ['key' => 'api_key', 'label' => 'Public Key', 'type' => 'text', 'required' => true],
                    ['key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Pronttus',
                'fields' => [
                    ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
                    ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'name' => 'Iron Pay',
                'fields' => [
                    ['key' => 'api_token', 'label' => 'Token da API', 'type' => 'password', 'required' => true],
                ],
            ],
        ];
    }
}
