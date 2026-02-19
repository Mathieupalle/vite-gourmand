<?php
require_once __DIR__ . '/src/mongo.php';

try {
    $client = mongo();
    $db = $client->vite_gourmand_stats;
    echo "MongoDB connecté avec succès.";
} catch (Exception $e) {
    echo "Erreur MongoDB : " . $e->getMessage();
}
