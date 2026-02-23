<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header('Location: ../');
    exit;
}

require_once __DIR__ . '/../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Required fields
    $name = trim($_POST['name'] ?? '');
    $provider_id = intval($_POST['provider_id'] ?? 0);

    require_once __DIR__ . '/../app/Services/PlanService.php';
    $planService = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);

    // Optional/Toggle fields
    $description = trim($_POST['description'] ?? '');
    $editable_amount = isset($_POST['editable_amount']) ? 1 : 0;

    if (!$planService->hasFeature('allow_editable_amount')) {
        $editable_amount = 0;
    }

    // Amount formatting (BRL to Decimal)
    $amount_raw = $_POST['amount'] ?? '0';
    $amount_raw = str_replace('.', '', $amount_raw); // remove thousands helper
    $amount_raw = str_replace(',', '.', $amount_raw); // comma to dot
    $amount = floatval($amount_raw);

    // Optional Settings
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $max_payments = !empty($_POST['max_payments']) ? intval($_POST['max_payments']) : null;
    $thank_you_url = trim($_POST['thank_you_url'] ?? '');
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

    // Generate Unique Slug
    function generateUniqueSlug($conn, $base)
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($base)));
        if (empty($slug))
            $slug = 'link';
        $original_slug = $slug;
        $counter = 1;

        while (true) {
            $stmt = $conn->prepare("SELECT id FROM payment_links WHERE slug = ?");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $stmt->close();
                return $slug;
            }
            $stmt->close();
            $slug = $original_slug . '-' . rand(100, 9999);
            $counter++;
        }
    }

    $slug = generateUniqueSlug($conn, $name);

    // Insert
    $sql = "INSERT INTO payment_links (user_id, name, description, amount, editable_amount, provider_id, slug, max_payments, expires_at, thank_you_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdiisissi", $user_id, $name, $description, $amount, $editable_amount, $provider_id, $slug, $max_payments, $expires_at, $thank_you_url, $status);

    if ($stmt->execute()) {
        header("Location: index.php?msg=success");
    }
    else {
        header("Location: index.php?msg=error");
    }
    $stmt->close();
}

$conn->close();
?>
