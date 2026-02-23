<?php
require_once __DIR__ . '/../../conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Running migrations...\n";

// 1. Create plans table
$sql1 = "CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql1)) {
    echo "Table 'plans' verified/created.\n";
}
else {
    echo "Error creating table 'plans': " . $conn->error . "\n";
}

// 2. Insert Base Plans
$conn->query("INSERT IGNORE INTO plans (id, name, price) VALUES (1, 'STARTER', 0.00), (2, 'PRO', 49.90)");
echo "Inserted default plans.\n";

// 3. Alter users table
$columns = [
    "plan_id" => "ADD COLUMN plan_id INT DEFAULT 1",
    "plan_expires_at" => "ADD COLUMN plan_expires_at DATETIME NULL",
    "subscription_status" => "ADD COLUMN subscription_status VARCHAR(20) DEFAULT 'active'"
];

foreach ($columns as $col => $alterQuery) {
    $check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE users $alterQuery")) {
            echo "Added column '$col' to 'users'.\n";
        }
        else {
            echo "Error adding column '$col': " . $conn->error . "\n";
        }
    }
    else {
        echo "Column '$col' already exists.\n";
    }
}

// 4. Set existing users to STARTER (id 1)
$conn->query("UPDATE users SET plan_id = 1 WHERE plan_id IS NULL");
$conn->query("UPDATE users SET subscription_status = 'active' WHERE subscription_status IS NULL");
echo "Existing users defaulted to STARTER.\n";

echo "Migration complete.\n";
$conn->close();
