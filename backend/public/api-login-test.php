<?php
// Script to simulate exact POST request to /api/v1/auth/login and inspect Symfony response
header('Content-Type: application/json; charset=utf-8');

$email = $_GET['email'] ?? 'admin@tijanesnours.lu';
$password = $_GET['password'] ?? 'adminpassword123';

$loginUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1') . '/api/v1/auth/login';

$payload = json_encode([
    'username' => $email,
    'email' => $email,
    'password' => $password
]);

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n" .
                     "Accept: application/json\r\n" .
                     "Content-Length: " . strlen($payload) . "\r\n",
        'content' => $payload,
        'ignore_errors' => true,
        'timeout' => 5
    ]
];

$context  = stream_context_create($options);
$response = @file_get_contents($loginUrl, false, $context);
$responseHeaders = $http_response_header ?? [];

$statusCode = 0;
if (!empty($responseHeaders[0])) {
    preg_match('{HTTP\/\S+\s+(\d+)}', $responseHeaders[0], $matches);
    $statusCode = (int)($matches[1] ?? 0);
}

$decodedBody = json_decode($response, true) ?? $response;

echo json_encode([
    'simulated_url' => $loginUrl,
    'sent_payload' => [
        'username' => $email,
        'email' => $email,
        'password' => '***'
    ],
    'symfony_response' => [
        'status_code' => $statusCode,
        'headers' => array_slice($responseHeaders, 0, 5),
        'body' => $decodedBody
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
