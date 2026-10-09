<?php
// Script to read the last 50 lines of Symfony prod.log / dev.log on OVH without SSH
header('Content-Type: application/json; charset=utf-8');

$logDir = __DIR__ . '/../var/log';
$files = glob($logDir . '/*.log');

$logs = [];

if (empty($files)) {
    echo json_encode([
        'status' => 'NO_LOGS_FOUND',
        'log_dir' => realpath($logDir) ?: $logDir,
        'message' => 'Aucun fichier .log trouvé dans var/log/.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

foreach ($files as $file) {
    $filename = basename($file);
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastLines = array_slice($lines, -40);
    $logs[$filename] = $lastLines;
}

echo json_encode([
    'status' => 'SUCCESS',
    'log_files_count' => count($files),
    'logs' => $logs
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
