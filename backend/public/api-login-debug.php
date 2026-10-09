<?php
// Debug script forcing APP_DEBUG=true to expose the exact 500 exception details
header('Content-Type: application/json; charset=utf-8');

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ErrorHandler\Debug;

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env.local';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv($envFile);
}

$email = $_GET['email'] ?? 'admin@tijanesnours.lu';
$password = $_GET['password'] ?? 'adminpassword123';

try {
    Debug::enable();
    $_SERVER['APP_ENV'] = 'dev';
    $_SERVER['APP_DEBUG'] = true;

    $kernel = new Kernel('dev', true);
    $kernel->boot();

    $payload = json_encode([
        'username' => $email,
        'email' => $email,
        'password' => $password
    ]);

    $request = Request::create(
        '/api/v1/auth/login',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ],
        $payload
    );

    $response = $kernel->handle($request);

    $statusCode = $response->getStatusCode();
    $content = $response->getContent();
    $jsonDecoded = json_decode($content, true);

    echo json_encode([
        'status_code' => $statusCode,
        'debug_response' => $jsonDecoded ?? $content
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'EXCEPTION_CAUGHT',
        'exception_class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 15)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
