<?php
// Comprehensive Diagnostic script to count rows and check JWT keys on OVH
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

// Check JWT Keys
$jwtPrivate = file_exists(__DIR__ . '/../config/jwt/private.pem');
$jwtPublic = file_exists(__DIR__ . '/../config/jwt/public.pem');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $tableCounts = [];
    $sampleData = [];

    foreach ($tables as $tbl) {
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$tbl`");
            $count = (int) $countStmt->fetchColumn();
            $tableCounts[$tbl] = $count;

            if ($count > 0 && in_array($tbl, ['user', 'users'])) {
                $sampleStmt = $pdo->query("SELECT id, email, roles FROM `$tbl` LIMIT 5");
                $sampleData['users'] = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($count > 0 && in_array($tbl, ['course_levels', 'course_level'])) {
                $sampleStmt = $pdo->query("SELECT id, name, target_age_min, target_age_max FROM `$tbl` LIMIT 5");
                $sampleData['course_levels'] = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $tableCounts[$tbl] = 'ERROR: ' . $e->getMessage();
        }
    }

    echo json_encode([
        'status' => 'SUCCESS',
        'database_name' => $dbname,
        'host' => $host,
        'jwt_keys' => [
            'private_pem_exists' => $jwtPrivate,
            'public_pem_exists' => $jwtPublic,
            'warning' => (!$jwtPrivate || !$jwtPublic) ? 'ATTENTION: Fichiers JWT .pem manquants sur le serveur !' : 'OK: Clés JWT trouvées.'
        ],
        'tables_row_counts' => $tableCounts,
        'sample_users_and_levels' => $sampleData
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Échec de la connexion MySQL',
        'error_details' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
