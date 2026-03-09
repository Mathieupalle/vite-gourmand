<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Exception;

final class UserService
{
    public function __construct(private UserRepository $repo) {}

    public function login(string $email, string $password): array
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide.");
        }
        if ($password === '') {
            throw new Exception("Mot de passe obligatoire.");
        }

        $user = $this->repo->findActiveByEmailWithRole($email);
        if (!$user) {
            throw new Exception("Email ou mot de passe incorrect.");
        }
        
        $stored = (string)$user['password'];
        $ok = false;

        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
            $ok = password_verify($password, $stored);
        } else {
            $ok = hash_equals($stored, $password);
        }

        if (!$ok) {
            throw new Exception("Email ou mot de passe incorrect.");
        }

        return [
            'id' => (int)$user['utilisateur_id'],
            'utilisateur_id' => (int)$user['utilisateur_id'],
            'email' => (string)$user['email'],
            'role' => (string)$user['role'],
            'role_id' => (int)$user['role_id'],
            'nom' => (string)($user['nom'] ?? ''),
            'prenom' => $user['prenom'],
            'telephone' => $user['telephone'],
            'ville' => $user['ville'],
            'adresse_postale' => $user['adresse_postale'],
        ];
    }

    public function register(array $post): array
    {
        $nom = trim((string)($post['nom'] ?? ''));
        $prenom = trim((string)($post['prenom'] ?? ''));
        $telephone = trim((string)($post['telephone'] ?? ''));
        $email = trim((string)($post['email'] ?? ''));
        $adresse = trim((string)($post['adresse_postale'] ?? ''));
        $password = (string)($post['password'] ?? '');

        if ($nom === '') throw new Exception("Nom obligatoire.");
        if ($prenom === '') throw new Exception("Prénom obligatoire.");
        if ($adresse === '') throw new Exception("Adresse postale obligatoire.");

        if ($telephone === '') {
            throw new Exception("Numéro de téléphone obligatoire.");
        }
        if (!preg_match('/^[0-9 +().-]{8,20}$/', $telephone)) {
            throw new Exception("Numéro de téléphone invalide.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide.");
        }

        if (!$this->isStrongPassword($password)) {
            throw new Exception("Mot de passe trop faible : 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.");
        }

        if ($this->repo->existsByEmail($email)) {
            throw new Exception("Email déjà utilisé.");
        }

        $userId = $this->repo->createUser([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'adresse_postale' => $adresse,
            'email' => $email,
            'password' => $password,
        ], 1);

        return [
            'id' => $userId,
            'utilisateur_id' => $userId,
            'email' => $email,
            'role' => 'user',
            'role_id' => 1,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'adresse_postale' => $adresse
        ];
    }

    public function createEmployee(string $email, string $password): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide.");
        }
        if (strlen($password) < 6) {
            throw new Exception("Mot de passe trop court.");
        }
        if ($this->repo->existsByEmail($email)) {
            throw new Exception("Email déjà utilisé.");
        }

        $this->repo->createUser([
            'email' => $email,
            'password' => $password,
            'nom' => '',
            'prenom' => null,
            'telephone' => null,
            'ville' => null,
            'adresse_postale' => null,
        ], 2);
    }

    public function requestReset(string $email): ?string
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide.");
        }

        $user = $this->repo->findActiveByEmailWithRole($email);
        if (!$user) {
            return null; // message neutre côté UI
        }

        $token = bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');
        $this->repo->setResetToken((int)$user['utilisateur_id'], $token, $expires);

        return $token;
    }

    public function resetPassword(string $token, string $pass1, string $pass2): void
    {
        $token = trim($token);
        if ($token === '') throw new Exception("Token manquant.");

        $row = $this->repo->findByResetToken($token);
        if (!$row) throw new Exception("Lien invalide ou déjà utilisé.");

        $expires = $row['reset_expires'] ?? null;
        if (!$expires || strtotime((string)$expires) < time()) {
            throw new Exception("Lien expiré. Merci de refaire une demande.");
        }

        if ($pass1 === '' || $pass2 === '') throw new Exception("Veuillez remplir les deux champs.");
        if ($pass1 !== $pass2) throw new Exception("Les mots de passe ne correspondent pas.");
        if (!$this->isStrongPassword($pass1)) {
            throw new Exception("Mot de passe trop faible : 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.");
        }

        $this->repo->updatePasswordAndClearReset((int)$row['utilisateur_id'], $pass1);
    }

    public function updateProfile(int $userId, array $post): array
    {
        $nom = trim((string)($post['nom'] ?? ''));
        $prenom = trim((string)($post['prenom'] ?? ''));
        $telephone = trim((string)($post['telephone'] ?? ''));
        $ville = trim((string)($post['ville'] ?? ''));
        $adresse = trim((string)($post['adresse_postale'] ?? ''));

        if ($nom === '') throw new Exception("Le nom est obligatoire.");
        if ($telephone !== '' && strlen($telephone) < 6) throw new Exception("Le téléphone semble trop court.");

        $this->repo->updateProfile($userId, [
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'ville' => $ville,
            'adresse_postale' => $adresse,
        ]);

        $fresh = $this->repo->findById($userId);
        if (!$fresh) throw new Exception("Utilisateur introuvable.");

        return $fresh;
    }

    private function isStrongPassword(string $password): bool
    {
        if (strlen($password) < 10) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
        return true;
    }
}