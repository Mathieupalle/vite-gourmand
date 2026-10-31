<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/helpers.php';

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testLeJetonEstCreeUneSeuleFoisParSession(): void
    {
        $jeton = csrf_token();

        $this->assertSame(64, strlen($jeton));
        $this->assertSame($jeton, csrf_token());
    }

    public function testLeChampCacheContientLeJeton(): void
    {
        $this->assertStringContainsString('value="' . csrf_token() . '"', csrf_field());
    }

    public function testUnJetonDifferentEstRefuse(): void
    {
        csrf_token();

        $this->assertFalse(csrf_valide('faux-jeton'));
        $this->assertFalse(csrf_valide(''));
    }

    public function testLeBonJetonEstAccepte(): void
    {
        $this->assertTrue(csrf_valide(csrf_token()));
    }
}
