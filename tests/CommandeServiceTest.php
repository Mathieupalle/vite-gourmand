<?php

use App\Service\CommandeService;
use PHPUnit\Framework\TestCase;

class CommandeServiceTest extends TestCase
{
    public function testPrixSansRemiseLivraisonABordeaux(): void
    {
        // 10 personnes x 30 € = 300 €, livraison gratuite à Bordeaux
        $prix = CommandeService::calculerPrix(30, 10, 10, 'Bordeaux', 0);

        $this->assertEquals(300, $prix['sousTotal']);
        $this->assertEquals(0, $prix['remise']);
        $this->assertEquals(0, $prix['prixLivraison']);
        $this->assertEquals(300, $prix['total']);
    }

    public function testRemiseDe10PourcentAPartirDe5PersonnesEnPlus(): void
    {
        // minimum 10 personnes, commande pour 15 : 450 € - 10 % = 405 €
        $prix = CommandeService::calculerPrix(30, 15, 10, 'Bordeaux', 0);

        $this->assertEquals(45, $prix['remise']);
        $this->assertEquals(405, $prix['total']);
    }

    public function testLivraisonHorsBordeaux(): void
    {
        // 5 € + 0,59 € x 10 km = 10,90 €
        $prix = CommandeService::calculerPrix(30, 10, 10, 'Mérignac', 10);

        $this->assertEqualsWithDelta(10.90, $prix['prixLivraison'], 0.01);
        $this->assertEqualsWithDelta(310.90, $prix['total'], 0.01);
    }

    public function testVilleBordeauxEnMajusculesAvecEspaces(): void
    {
        $prix = CommandeService::calculerPrix(30, 10, 10, '  BORDEAUX ', 25);

        $this->assertEquals(0, $prix['prixLivraison']);
    }
}
