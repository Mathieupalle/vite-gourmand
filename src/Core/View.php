<?php
namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): void
    {
        // Extraction des variables pour la vue
        extract($data, EXTR_SKIP);

        // Chemin complet du fichier template
        $path = __DIR__ . '/../../templates/' . $template . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("Vue introuvable : $path");
        }

        // Inclusion du template
        require $path;
    }

    // Redirection vers une route ou URL
    public static function redirect(string $path): void
    {
        header('Location: ' . rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'));
        exit;
    }
}