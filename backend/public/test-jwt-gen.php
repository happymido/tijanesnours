<?php
// Test JWT Token Manager generation directly to expose any hidden Lexik/OpenSSL/Key Exception
header('Content-Type: application/json; charset=utf-8');

use App\Kernel;
use App\IdentityAccess\Domain\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env.local';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv($envFile);
}

try {
    $kernel = new Kernel('dev', true);
    $kernel->boot();

    $container = $kernel->getContainer();
    $em = $container->get('doctrine')->getManager();

    // 1. Fetch Admin User
    $user = $em->getRepository(User::class)->findOneBy(['email' => 'admin@tijanesnours.lu']);

    if (!$user) {
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Utilisateur admin@tijanesnours.lu introuvable dans la table users via Doctrine ORM.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Fetch JWT Manager from Container
    $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');

    // 3. Generate Token
    $token = $jwtManager->create($user);

    echo json_encode([
        'status' => 'SUCCESS',
        'message' => 'Génération du Token JWT réussie à 100% !',
        'user_id' => $user->getId(),
        'user_email' => $user->getEmail(),
        'generated_token_sample' => substr($token, 0, 30) . '...'
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
