<?php
session_start();
$_SESSION['email'] = 'mathgoldyoficial@gmail.com';
chdir('c:\xampp\htdocs\uranoPAY\web\checkout-build');
try {
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    echo "SUCCESS. Rendered length: " . strlen($output) . "\n";
}
catch (Throwable $e) {
    echo "FATAL ERROR CAUGHT: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
