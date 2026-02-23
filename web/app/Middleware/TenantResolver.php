<?php
namespace App\Middleware;

use Exception;
use mysqli;

class TenantResolver
{
    private mysqli $db;
    private string $baseDomain;

    public function __construct(mysqli $db, string $baseDomain = 'amyxcheckout.com.br')
    {
        $this->db = $db;
        $this->baseDomain = $baseDomain;
    }

    public function resolve(): array
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $host = explode(':', strtolower(trim($host)))[0];

        if (empty($host)) {
            $this->abort(400, 'Invalid Host Request');
        }

        // Dev local: retorna como saas_admin para permitir acesso ao painel
        if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, '192.168.') === 0) {
            return ['type' => 'saas_admin', 'domain_name' => $host];
        }

        if ($host === $this->baseDomain || $host === "www." . $this->baseDomain) {
            return ['type' => 'saas_admin', 'domain_name' => $host];
        }

        $tenantData = null;

        if (str_ends_with($host, "." . $this->baseDomain)) {
            $subdomain = str_replace("." . $this->baseDomain, "", $host);
            $tenantData = $this->getDomainFromDb($subdomain, 'subdomain');
        }
        else {
            $tenantData = $this->getDomainFromDb($host, 'custom');
        }

        if (!$tenantData) {
            $this->abort(404, 'Dominio nao configurado na plataforma.');
        }

        if ($tenantData['status'] !== 'active') {
            $this->abort(403, 'Acesso bloqueado: dominio pendente ou suspenso.');
        }

        return [
            'type' => 'tenant',
            'user_id' => $tenantData['user_id'],
            'domain_name' => $host,
            'configs' => $tenantData
        ];
    }

    private function getDomainFromDb(string $domainStr, string $type)
    {
        $stmt = $this->db->prepare("SELECT * FROM domains WHERE domain = ? AND type = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ss", $domainStr, $type);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $data;
        }
        return null;
    }

    private function abort(int $code, string $msg)
    {
        http_response_code($code);
        exit("<h1>Erro {$code}</h1><p>{$msg}</p>");
    }
}