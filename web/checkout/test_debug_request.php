<?php
// Valid checkout ID and provider from previous context (ID 14 has provider configured?)
// Actually ID 1 is 'visagra' and has key_gateway.
// Let's try to simulate a request for checkout_id = 1.

$url = 'http://localhost/uranoPAY/web/checkout/process_payment.php';

$data = [
    'name' => 'Teste Debug',
    'document' => '12345678900',
    'amount' => 10.00,
    'checkout_id' => 1 // ID 1 is active in checkout_build
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Fetch content even on 4xx/5xx
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$headers = $http_response_header;

echo "HTTP Headers:\n";
print_r($headers);
echo "\nResponse Body:\n";
echo $result;
