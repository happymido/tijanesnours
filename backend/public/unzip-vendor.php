<?php
// Script to safely extract vendor.zip on OVH to fix corrupted vendor files
header('Content-Type: application/json; charset=utf-8');

$zipPath = __DIR__ . '/../vendor.zip';
$targetDir = __DIR__ . '/../';

if (!file_exists($zipPath)) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Fichier backend/vendor.zip introuvable sur le serveur. Veuillez envoyer vendor.zip via FileZilla dans le dossier backend/.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!class_exists('ZipArchive')) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'L\'extension PHP ZipArchive n\'est pas activée sur OVH.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $zip = new ZipArchive();
    $res = $zip->open($zipPath);

    if ($res === TRUE) {
        $zip->extractTo($targetDir);
        $zip->close();

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => 'Le dossier vendor.zip a été extrait avec succès et sans corruption !'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Échec d\'ouverture du fichier vendor.zip (code: ' . $res . ')'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
