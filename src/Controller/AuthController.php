<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Infrastructure\Database;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Security\Auth;

final class AuthController
{
    private UserService $service;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->service = new UserService(new UserRepository($pdo));
    }

    // Affiche le formulaire de connexion
    public function login(): void
    {
        if (Auth::isLoggedIn()) {
            View::redirect('/home');
        }

        View::render('auth/login', ['errors' => []]);
    }

    // Traitement du login
    public function handleLogin(array $post): void
    {
        $errors = [];

        try {
            $sessionUser = $this->service->login(
                (string)($post['email'] ?? ''),
                (string)($post['password'] ?? '')
            );

            session_start();
            $_SESSION['user'] = $sessionUser;

            View::redirect('/home');
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
            View::render('auth/login', ['errors' => $errors]);
        }
    }

    // Déconnexion
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        View::redirect('/home');
    }

    // Formulaire d'inscription
    public function register(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $sessionUser = $this->service->register($_POST);
                $_SESSION['user'] = $sessionUser;
                $success = "Inscription réussie. Un email de bienvenue vous a été envoyé.";
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('auth/register', compact('errors', 'success'));
    }

    // Mot de passe oublié
    public function forgotPassword(): void
    {
        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $token = $this->service->requestReset((string)($_POST['email'] ?? ''));
                $success = "Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";

                if ($token) {
                    $base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                    $_SESSION['reset_link_demo'] = $base . "/resetPassword?token=" . urlencode($token);
                }
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('auth/forgotPassword', compact('errors', 'success'));
    }

    // Réinitialisation du mot de passe
    public function resetPassword(): void
    {
        if (empty($_GET['token'])) {
            http_response_code(400);
            exit("Token manquant.");
        }

        $token = $_GET['token'];
        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->service->resetPassword(
                    $token,
                    (string)($_POST['password'] ?? ''),
                    (string)($_POST['password_confirm'] ?? '')
                );
                $success = "Mot de passe mis à jour. Vous pouvez vous connecter.";
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('auth/resetPassword', compact('errors', 'success', 'token'));
    }
}