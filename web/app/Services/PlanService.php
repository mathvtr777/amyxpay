<?php
namespace App\Services;

class PlanService
{
    private $conn;
    private $userId;
    private $planData;
    private $userStatus;
    private $expiresAt;

    public function __construct($conn, $userId)
    {
        $this->conn = $conn;

        // Re-establish connection if it was closed prematurely by the parent script
        $connectionAlive = false;
        try {
            $connectionAlive = @$this->conn->ping();
        }
        catch (\Throwable $e) {
            $connectionAlive = false;
        }

        if (!$connectionAlive) {
            require __DIR__ . '/../../conectarbanco.php';
            $this->conn = new \mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
        }

        $this->userId = $userId;
        $this->loadUserData();
    }

    private function loadUserData()
    {
        if (!$this->userId)
            return;

        $sql = "SELECT p.id as plan_id, p.name as plan_name, u.subscription_status, u.plan_expires_at 
                FROM users u 
                LEFT JOIN plans p ON u.plan_id = p.id 
                WHERE u.id = ? OR u.user_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $this->userId, $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->planData = [
                'id' => $row['plan_id'] ?? 1,
                'name' => strtoupper($row['plan_name'] ?? 'STARTER')
            ];
            $this->userStatus = $row['subscription_status'] ?? 'active';
            $this->expiresAt = $row['plan_expires_at'];
        }
        else {
            // Default fallback
            $this->planData = ['id' => 1, 'name' => 'STARTER'];
            $this->userStatus = 'active';
            $this->expiresAt = null;
        }
        $stmt->close();
    }

    public function getPlanName()
    {
        return $this->planData['name'];
    }

    public function getPlanId()
    {
        return $this->planData['id'];
    }

    public function isPro()
    {
        return $this->planData['id'] == 2;
    }

    public function isActive()
    {
        if ($this->userStatus !== 'active')
            return false;

        if (!empty($this->expiresAt)) {
            $now = new \DateTime();
            $expires = new \DateTime($this->expiresAt);
            if ($now > $expires)
                return false;
        }
        return true;
    }

    // Permission Definitions
    public function hasFeature($featureKey)
    {
        $features = [
            'allow_split' => [1 => false, 2 => true],
            'allow_multiple_providers' => [1 => false, 2 => true],
            'allow_editable_amount' => [1 => false, 2 => true],
            'allow_advanced_pixels' => [1 => false, 2 => true],
            'remove_branding' => [1 => false, 2 => true]
        ];

        if (isset($features[$featureKey][$this->planData['id']])) {
            return $features[$featureKey][$this->planData['id']];
        }
        return false;
    }

    // Limits Definitions (-1 = unlimited)
    public function getLimit($limitKey)
    {
        $limits = [
            'max_checkouts' => [1 => 10, 2 => -1],
            'max_domains' => [1 => 1, 2 => -1]
        ];

        if (isset($limits[$limitKey][$this->planData['id']])) {
            return $limits[$limitKey][$this->planData['id']];
        }
        return 0;
    }

    public function checkLimit($limitKey, $currentCount)
    {
        $limit = $this->getLimit($limitKey);
        if ($limit === -1)
            return true;
        return $currentCount < $limit;
    }
}
