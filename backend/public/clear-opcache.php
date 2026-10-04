<?php
// OPcache reset script for OVH shared hosting
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo json_encode([
        'status' => 'SUCCESS',
        'message' => 'PHP OPcache de OVH a été vidé avec succès !'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'status' => 'INFO',
        'message' => 'OPcache n\'est pas actif sur cette instance PHP.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
