<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findActiveByEmailWithRole(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.utilisateur_id, u.email, u.password, u.role_id, u.actif, u.nom, u.prenom, u.telephone, u.ville, u.adresse_postale,
                   r.libelle AS role
            FROM utilisateur u
            JOIN role r ON r.role_id = u.role_id
            WHERE u.email = ? AND u.actif = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    public function createUser(array $data, int $roleId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO utilisateur (nom, prenom, telephone, ville, adresse_postale, email, password, role_id, actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $data['nom'] ?? '',
            $data['prenom'] ?? null,
            $data['telephone'] ?? null,
            $data['ville'] ?? null,
            $data['adresse_postale'] ?? null,
            $data['email'],
            $data['password'],
            $roleId
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT utilisateur_id, email, nom, prenom, telephone, ville, adresse_postale, role_id, actif
            FROM utilisateur
            WHERE utilisateur_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET nom = ?, prenom = ?, telephone = ?, ville = ?, adresse_postale = ?
            WHERE utilisateur_id = ?
        ");
        $stmt->execute([
            $data['nom'],
            $data['prenom'] ?: null,
            $data['telephone'] ?: null,
            $data['ville'] ?: null,
            $data['adresse_postale'] ?: null,
            $userId
        ]);
    }

    public function setResetToken(int $userId, string $token, string $expires): void
    {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expires = ? WHERE utilisateur_id = ?");
        $stmt->execute([$token, $expires, $userId]);
    }

    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT utilisateur_id, reset_expires
            FROM utilisateur
            WHERE reset_token = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function updatePasswordAndClearReset(int $userId, string $newPassword): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE utilisateur_id = ?
        ");
        $stmt->execute([$newPassword, $userId]);
    }

    // Employés = role_id = 2
    public function listEmployees(): array
    {
        $stmt = $this->pdo->query("
            SELECT utilisateur_id, email, actif
            FROM utilisateur
            WHERE role_id = 2
            ORDER BY utilisateur_id DESC
        ");
        return $stmt->fetchAll() ?: [];
    }

    public function deactivateUser(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET actif = 0 WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
    }
}