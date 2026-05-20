<?php
/**
 * Comptes utilisateurs (connexion, rôles).
 */

declare(strict_types=1);

namespace Moncine;

use PDO;

final class UtilisateurRepository
{
  /** Longueur minimale du mot de passe. */
    public const MIN_PASSWORD_LENGTH = 8;

  /** Limite pour éviter les abus (charge CPU du hachage). */
    public const MAX_PASSWORD_LENGTH = 128;

    private const PUBLIC_COLUMNS = 'id, nom, prenom, pseudo, email, role, actif, foyer_id, last_login_at, created_at';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function countWithPassword(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM utilisateurs WHERE TRIM(password_hash) != '' AND actif = 1"
        )->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function findByEmail(string $email): ?array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');
        if ($email === '') {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM utilisateurs WHERE LOWER(TRIM(email)) = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** @return array<string, mixed>|null Ligne avec password_hash (connexion uniquement). */
    public function findByEmailForAuthentication(string $email): ?array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');
        if ($email === '') {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT id, nom, email, password_hash, role, actif FROM utilisateurs
             WHERE LOWER(TRIM(email)) = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** @return list<array<string, mixed>> */
    public function listAll(): array
    {
        return $this->db->query(
            'SELECT u.id, u.nom, u.prenom, u.pseudo, u.email, u.role, u.actif, u.foyer_id, u.last_login_at, u.created_at,
                    f.nom AS foyer_nom
             FROM utilisateurs u
             LEFT JOIN foyers f ON f.id = u.foyer_id
             ORDER BY u.role DESC, u.nom COLLATE FRENCH_NOCASE'
        )->fetchAll();
    }

    public function create(
        string $nom,
        string $email,
        string $plainPassword,
        string $role,
        int $foyerId = 0,
        string $prenom = '',
        string $pseudo = ''
    ): int|string {
        $nom = trim($nom);
        $prenom = trim($prenom);
        $pseudo = UserProfile::sanitizePseudo($pseudo);
        $email = mb_strtolower(trim($email), 'UTF-8');
        $role = UserRole::normalize($role);

        $identity = UserProfile::validateIdentityFields($nom, $prenom, $pseudo);
        if ($identity !== true) {
            return $identity;
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Adresse e-mail invalide.';
        }
        if ($this->findByEmail($email) !== null) {
            return 'Cette adresse e-mail est déjà utilisée.';
        }
        $hash = self::hashPassword($plainPassword);
        if ($hash === null) {
            return self::passwordValidationMessage();
        }

        if ($foyerId <= 0) {
            $foyers = (new FoyerRepository())->listAll();
            if ($foyers !== []) {
                $foyerId = (int) ($foyers[0]['id'] ?? 0);
            }
        } elseif ((new FoyerRepository())->findById($foyerId) === null) {
            return 'Foyer introuvable.';
        }

        $this->db->prepare(
            'INSERT INTO utilisateurs (nom, prenom, pseudo, email, password_hash, role, actif, foyer_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?, datetime(\'now\'))'
        )->execute([
            $nom,
            $prenom,
            $pseudo,
            $email,
            $hash,
            $role,
            $foyerId > 0 ? $foyerId : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Premier compte administrateur (installation).
     *
     * @return int|string ID utilisateur ou message d’erreur
     */
    public function createFirstAdmin(string $nom, string $email, string $plainPassword): int|string
    {
        $this->db->beginTransaction();
        try {
            if ($this->countWithPassword() > 0) {
                $this->db->rollBack();

                return 'Un compte administrateur existe déjà. Utilisez la page de connexion.';
            }

            $result = $this->create($nom, $email, $plainPassword, UserRole::ADMIN);
            if (!is_int($result)) {
                $this->db->rollBack();

                return $result;
            }

            if (FoyerRepository::tableExists($this->db)) {
                (new FoyerRepository())->createDefaultForUser($result);
            }

            $this->db->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return 'Création du compte impossible.';
        }
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->prepare(
            'UPDATE utilisateurs SET last_login_at = datetime(\'now\') WHERE id = ?'
        )->execute([$id]);
    }

    /**
     * @return true|string
     */
    public function canSetActive(int $id, bool $active): bool|string
    {
        if ($active) {
            return true;
        }

        $user = $this->findById($id);
        if ($user === null) {
            return 'Compte introuvable.';
        }

        if (UserRole::isAdmin((string) ($user['role'] ?? '')) && $this->countAdmins() <= 1) {
            return 'Impossible de désactiver le dernier administrateur actif.';
        }

        return true;
    }

    public function setActive(int $id, bool $active): bool
    {
        if ($id <= 0 || $this->canSetActive($id, $active) !== true) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE utilisateurs SET actif = ? WHERE id = ?');
        $stmt->execute([$active ? 1 : 0, $id]);

        return $stmt->rowCount() > 0;
    }

    public function upgradePasswordHashIfNeeded(int $userId, string $currentHash, string $plainPassword): void
    {
        if ($userId <= 0 || $currentHash === '' || $plainPassword === '') {
            return;
        }
        if (!password_needs_rehash($currentHash, PASSWORD_DEFAULT)) {
            return;
        }
        $newHash = self::hashPassword($plainPassword);
        if ($newHash === null) {
            return;
        }
        $this->db->prepare('UPDATE utilisateurs SET password_hash = ? WHERE id = ?')
            ->execute([$newHash, $userId]);
    }

    public function countAdmins(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin' AND actif = 1"
        )->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listActiveAdmins(): array
    {
        $stmt = $this->db->query(
            'SELECT ' . self::PUBLIC_COLUMNS . "
             FROM utilisateurs
             WHERE role = 'admin' AND actif = 1
             ORDER BY nom COLLATE FRENCH_NOCASE"
        );

        return $stmt ? ($stmt->fetchAll() ?: []) : [];
    }

    public function countLibraryEntries(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }
        $foyerId = (new FoyerRepository())->currentFoyerIdForUser($userId);
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM bibliotheque WHERE user_id = ? AND statut = ?'
        );
        $stmt->execute([$userId, LibraryStatut::WISHLIST]);
        $wishlist = (int) $stmt->fetchColumn();

        if ($foyerId <= 0) {
            return $wishlist;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM bibliotheque WHERE foyer_id = ? AND statut = ?'
        );
        $stmt->execute([$foyerId, LibraryStatut::COLLECTION]);

        return $wishlist + (int) $stmt->fetchColumn();
    }

    /**
     * Supprime un compte et toute sa bibliothèque (films, envies, historique de vision).
     *
     * @return true|string
     */
    public function delete(int $id): bool|string
    {
        if ($id <= 0) {
            return 'Compte invalide.';
        }

        $user = $this->findById($id);
        if ($user === null) {
            return 'Compte introuvable.';
        }

        if (UserRole::isAdmin((string) ($user['role'] ?? '')) && $this->countAdmins() <= 1) {
            return 'Impossible de supprimer le dernier administrateur actif.';
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare('DELETE FROM historique WHERE user_id = ?')->execute([$id]);
            $this->db->prepare(
                'DELETE FROM bibliotheque WHERE user_id = ? AND statut = ?'
            )->execute([$id, LibraryStatut::WISHLIST]);
            $stmt = $this->db->prepare('DELETE FROM utilisateurs WHERE id = ?');
            $stmt->execute([$id]);
            if ($stmt->rowCount() < 1) {
                $this->db->rollBack();

                return 'Compte introuvable.';
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Moncine delete user #' . $id . ': ' . $e->getMessage());

            return 'Suppression impossible. Réessayez ou consultez les logs du serveur.';
        }

        return true;
    }

    public static function hashPassword(string $plain): ?string
    {
        $len = strlen($plain);
        if ($len < self::MIN_PASSWORD_LENGTH || $len > self::MAX_PASSWORD_LENGTH) {
            return null;
        }

        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function passwordValidationMessage(): string
    {
        return 'Mot de passe invalide (' . self::MIN_PASSWORD_LENGTH . ' à ' . self::MAX_PASSWORD_LENGTH . ' caractères).';
    }

    public static function verifyPassword(array $user, string $plain): bool
    {
        $hash = (string) ($user['password_hash'] ?? '');
        if ($hash === '' || $plain === '') {
            return false;
        }

        return password_verify($plain, $hash);
    }

    /** @return array<string, mixed>|null */
    public function findByIdForAuthentication(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT id, nom, email, password_hash, role, actif FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * @return true|string
     */
    public function updateProfile(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        string $pseudo = ''
    ): bool|string {
        $nom = trim($nom);
        $prenom = trim($prenom);
        $pseudo = UserProfile::sanitizePseudo($pseudo);
        $email = mb_strtolower(trim($email), 'UTF-8');

        if ($id <= 0) {
            return 'Compte invalide.';
        }

        $identity = UserProfile::validateIdentityFields($nom, $prenom, $pseudo);
        if ($identity !== true) {
            return $identity;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Adresse e-mail invalide.';
        }

        $existing = $this->findByEmail($email);
        if ($existing !== null && (int) ($existing['id'] ?? 0) !== $id) {
            return 'Cette adresse e-mail est déjà utilisée.';
        }

        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET nom = ?, prenom = ?, pseudo = ?, email = ? WHERE id = ?'
        );
        $stmt->execute([$nom, $prenom, $pseudo, $email, $id]);

        return $stmt->rowCount() > 0 ? true : 'Compte introuvable.';
    }

    /**
     * @return true|string
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool|string
    {
        $user = $this->findByIdForAuthentication($id);
        if ($user === null) {
            return 'Compte introuvable.';
        }
        if (!self::verifyPassword($user, $currentPassword)) {
            return 'Mot de passe actuel incorrect.';
        }

        $hash = self::hashPassword($newPassword);
        if ($hash === null) {
            return self::passwordValidationMessage();
        }

        $this->db->prepare('UPDATE utilisateurs SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $id]);

        return true;
    }

    /**
     * Mot de passe provisoire (affiché une seule fois par l’administrateur).
     *
     * @return array{password: string}|string
     */
    public function adminSetTemporaryPassword(int $id): array|string
    {
        if ($id <= 0) {
            return 'Compte invalide.';
        }
        if ($this->findById($id) === null) {
            return 'Compte introuvable.';
        }

        $plain = self::generateTemporaryPassword();
        $hash = self::hashPassword($plain);
        if ($hash === null) {
            return 'Génération du mot de passe impossible.';
        }

        $this->db->prepare('UPDATE utilisateurs SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $id]);

        return ['password' => $plain];
    }

    public static function generateTemporaryPassword(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $len = strlen($chars);
        $out = '';
        for ($i = 0; $i < 12; $i++) {
            $out .= $chars[random_int(0, $len - 1)];
        }

        return $out;
    }

    /**
     * Demande de réinitialisation par e-mail (message toujours neutre côté appelant).
     */
    public function requestPasswordResetEmail(string $email): void
    {
        $email = mb_strtolower(trim($email), 'UTF-8');
        if ($email === '' || PasswordResetThrottle::isBlocked($email)) {
            PasswordResetThrottle::recordAttempt($email);

            return;
        }

        PasswordResetThrottle::recordAttempt($email);

        $user = $this->findByEmailForAuthentication($email);
        if ($user === null || (int) ($user['actif'] ?? 0) !== 1) {
            return;
        }

        $tokenRepo = new PasswordResetRepository();
        $tokenRepo->purgeExpired();
        $plain = $tokenRepo->createForUser((int) $user['id']);
        if ($plain === null) {
            return;
        }

        $url = AppUrl::path('/reinitialiser-mot-de-passe.php?token=' . rawurlencode($plain));
        MailService::sendPasswordReset(
            $email,
            (string) ($user['nom'] ?? ''),
            $url
        );
    }
}
