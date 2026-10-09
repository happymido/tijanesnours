<?php
/**
 * Écouteur Webhook Git pour Déploiement Automatique sur OVH
 * Emplacement : backend/public/webhook.php
 */

// Définir le dossier racine du projet Git (2 niveaux au-dessus de backend/public)
$projectDir = realpath(__DIR__ . '/../../');

$output = [];
$returnVar = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['exec'])) {
    // Se déplacer dans la racine du dépôt, récupérer les derniers commits et forcer la mise à jour
    $command = "cd " . escapeshellarg($projectDir) . " && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1";
    exec($command, $output, $returnVar);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $returnVar === 0 ? 'success' : 'error',
        'code' => $returnVar,
        'message' => $returnVar === 0 ? 'Synchronisation Git réussie !' : 'Erreur lors du git pull',
        'output' => $output,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'online',
    'message' => 'Écouteur Webhook Tijanes Nours opérationnel.',
    'project_dir' => $projectDir
], JSON_PRETTY_PRINT);
