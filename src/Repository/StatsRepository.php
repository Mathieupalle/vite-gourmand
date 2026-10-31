<?php
declare(strict_types=1);

namespace App\Repository;

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

final class StatsRepository
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // Liste des menus présents dans MongoDB (pour le filtre)
    public function getMenus(): array
    {
        $col = $this->client->selectCollection('vitegourmand', 'orders_analytics');

        $resultat = $col->aggregate([
            ['$group' => [
                '_id' => '$menuId',
                'titre' => ['$first' => '$menuTitre'],
            ]],
            ['$sort' => ['titre' => 1]],
        ]);

        $menus = [];
        foreach ($resultat as $ligne) {
            $menus[] = [
                'menu_id' => (int)$ligne['_id'],
                'titre' => (string)$ligne['titre'],
            ];
        }

        return $menus;
    }

    // Nombre de commandes et chiffre d'affaires par menu sur une période
    // $menuId = 0 : tous les menus
    public function getStatsParMenu(string $dateDebut, string $dateFin, int $menuId): array
    {
        $col = $this->client->selectCollection('vitegourmand', 'orders_analytics');

        // MongoDB attend des dates en millisecondes
        $debut = new UTCDateTime(strtotime($dateDebut . ' 00:00:00') * 1000);
        $fin = new UTCDateTime(strtotime($dateFin . ' 23:59:59') * 1000);

        // Filtre : période choisie et commandes non annulées
        $filtre = [
            'dateCommande' => ['$gte' => $debut, '$lte' => $fin],
            'statut' => ['$ne' => 'annulee'],
        ];

        if ($menuId > 0) {
            $filtre['menuId'] = $menuId;
        }

        $resultat = $col->aggregate([
            ['$match' => $filtre],
            ['$group' => [
                '_id' => '$menuId',
                'titre' => ['$first' => '$menuTitre'],
                'nbCommandes' => ['$sum' => 1],
                'chiffreAffaires' => ['$sum' => '$total'],
            ]],
            ['$sort' => ['titre' => 1]],
        ]);

        $stats = [];
        foreach ($resultat as $ligne) {
            $stats[] = [
                'titre' => (string)$ligne['titre'],
                'nbCommandes' => (int)$ligne['nbCommandes'],
                'chiffreAffaires' => (float)$ligne['chiffreAffaires'],
            ];
        }

        return $stats;
    }
}
