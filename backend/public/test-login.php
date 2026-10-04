<?php
// Diagnostic script to test authentication steps on OVH using the 'users' table
header('Content-Type: application/json; charset=utf-8');

$envFile = __DIR__ . '/../.env.local';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}

$dbUrl = null;
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), 'DATABASE_URL=')) {
            $dbUrl = trim(str_replace('DATABASE_URL=', '', $line), '"\'');
            break;
        }
    }
}

if (!$dbUrl) {
    echo json_encode(['status' => 'ERROR', 'message' => 'DATABASE_URL introuvable']);
    exit;
}

$parsed = parse_url($dbUrl);
$host = $parsed['host'] ?? '127.0.0.1';
$port = $parsed['port'] ?? 3306;
$user = $parsed['user'] ?? '';
$pass = $parsed['pass'] ?? '';
$dbname = ltrim($parsed['path'] ?? '', '/');

$emailTested = $_GET['email'] ?? 'admin@tijanesnours.lu';
$passwordTested = $_GET['password'] ?? 'adminpassword123';

$results = [
    'step1_database_connection' => false,
    'step2_user_found_in_db' => false,
    'step3_password_verify_match' => false,
    'step4_openssl_private_key_readable' => false,
    'details' => []
];

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $results['step1_database_connection'] = true;

    // Query 'users' (plural) table
    $stmt = $pdo->prepare("SELECT id, email, password, roles FROM `users` WHERE email = :email");
    $stmt->execute(['email' => $emailTested]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $results['step2_user_found_in_db'] = true;
        $results['details']['user_id'] = $userData['id'];
        $results['details']['user_roles'] = $userData['roles'];
        $results['details']['password_hash_sample'] = substr($userData['password'], 0, 15) . '...';

        $passwordMatch = password_verify($passwordTested, $userData['password']);
        $results['step3_password_verify_match'] = $passwordMatch;
        if (!$passwordMatch) {
            $results['details']['password_error'] = "Le mot de passe '{$passwordTested}' ne correspond pas au hash enregistré dans la table users.";
        }
    } else {
        $results['details']['user_error'] = "Aucun utilisateur trouvé avec l'email '{$emailTested}' dans la table users.";
    }

    // Test OpenSSL Private Key loading
    $privateKeyPath = __DIR__ . '/../config/jwt/private.pem';
    if (file_exists($privateKeyPath)) {
        $pemContent = file_get_contents($privateKeyPath);
        $res = openssl_pkey_get_private($pemContent, 'tijanes_jwt_secret_key_passphrase');
        if ($res !== false) {
            $results['step4_openssl_private_key_readable'] = true;
        } else {
            $results['details']['openssl_error'] = 'OpenSSL n\'a pas pu lire private.pem avec la passphrase: ' . openssl_error_string();
        }
    } else {
        $results['details']['jwt_file_error'] = "Fichier {$privateKeyPath} introuvable.";
    }

    echo json_encode([
        'status' => ($results['step1_database_connection'] && $results['step2_user_found_in_db'] && $results['step3_password_verify_match'] && $results['step4_openssl_private_key_readable']) ? 'SUCCESS' : 'FAILURE',
        'tested_email' => $emailTested,
        'results' => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
