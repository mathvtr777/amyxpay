<?php
namespace App\Services;

class DomainService
{
    private string $serverIp;

    public function __construct(string $serverIp)
    {
        $this->serverIp = $serverIp; // Ex: '72.60.244.72'
    }

    /**
     * Valida se o domínio DNS do usuário ressove para a VPS
     */
    public function verifyDns(string $domain): bool
    {
        $domain = $this->sanitizeDomain($domain);

        // Pega registros do tipo 'A' configurados no provedor do cliente
        $records = dns_get_record($domain, DNS_A);

        if (empty($records)) {
            return false;
        }

        foreach ($records as $record) {
            if (isset($record['ip']) && $record['ip'] === $this->serverIp) {
                return true; // Apontamento correto!
            }
        }

        return false;
    }

    /**
     * Chama Certbot CLI local do Ubuntu para gerar o Let's Encrypt para o domínio do cliente
     */
    public function activateCustomSsl(string $domain): bool
    {
        $domain = $this->sanitizeDomain($domain);

        if (!$this->verifyDns($domain)) {
            throw new \Exception("Domínio ainda não propaga para o IP da nossa VPS.");
        }

        // IMPORTANTE: Para isso funcionar, o arquivo /etc/sudoers da VPS deve permitir
        // que o usuário do PHP (www-data) execute certbot sem pedir senha.
        // Comando linux no ssh: visudo -> Adicione no fim: www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot

        $command = escapeshellcmd("sudo certbot --nginx -d " . escapeshellarg($domain) . " --non-interactive --agree-tos -m contato@amyxcheckout.com.br --redirect");
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            // Sucesso!
            return true;
        }

        error_log("Falha ao gerar SSL: " . implode("\n", $output));
        return false;
    }

    public function sanitizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain); // Tira protocolos
        $domain = explode('/', $domain)[0]; // Tira rotas ex: .com/checkout

        // Remover www. opcionalmente (ou redirecionar depois)
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }

        return filter_var($domain, FILTER_SANITIZE_URL);
    }
}
