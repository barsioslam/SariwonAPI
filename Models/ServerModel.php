<?php

namespace SariwonAPI\Models;

class ServerModel extends Model {

    public function __construct() {
        parent::__construct('server');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT s.*, ts.title AS theme_title,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id) AS party_count
             FROM `server` s
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             WHERE s.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne() ?: null;
    }

    /**
     * Remonte tous les serveurs pertinents pour l'utilisateur :
     * ceux qu'il a créés, mis en favoris, ou dans lesquels il est dans une partie.
     */
    public function findForUser(int $userId): array {
        $this->query(
            'SELECT s.*,
                    sf.saved_at,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id) AS party_count,
                    IF(s.created_by = :uid,  1, 0) AS is_owner,
                    IF(sf.id IS NOT NULL,     1, 0) AS is_favorited,
                    IF(pm.server_id IS NOT NULL, 1, 0) AS is_party_member
             FROM `server` s
             LEFT JOIN `server_favorite` sf ON sf.server_id = s.id AND sf.user_id = :uid2
             LEFT JOIN (
                 SELECT DISTINCT p.server_id
                 FROM `party_member` pm2
                 JOIN `party` p ON p.id = pm2.party_id
                 WHERE pm2.user_id = :uid3
             ) pm ON pm.server_id = s.id
             WHERE s.created_by = :uid4
                OR sf.id IS NOT NULL
                OR pm.server_id IS NOT NULL
             ORDER BY COALESCE(sf.saved_at, s.created_at) DESC',
            ['uid' => $userId, 'uid2' => $userId, 'uid3' => $userId, 'uid4' => $userId]
        );
        return $this->fetchAll();
    }

    /** Serveurs publics (tous les serveurs sont désormais browsables) */
    public function findPublic(int $limit = 50, int $offset = 0): array {
        $this->query(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id) AS party_count,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id AND p.is_active = 1) AS active_parties
             FROM `server` s
             ORDER BY s.created_at DESC
             LIMIT :limit OFFSET :offset',
            ['limit' => $limit, 'offset' => $offset]
        );
        return $this->fetchAll();
    }

    public function create(
        int     $createdBy,
        string  $name,
        string  $description,
        bool    $ageRestricted,
        ?string $genre,
        ?int    $themeId = null
    ): int {
        $this->query(
            'INSERT INTO `server`
                (`created_by`, `name`, `description`, `age_restricted`, `genre`, `server_theme_id`, `created_at`)
             VALUES
                (:created_by, :name, :description, :age_restricted, :genre, :theme_id, :created_at)',
            [
                'created_by'     => $createdBy,
                'name'           => $name,
                'description'    => $description,
                'age_restricted' => (int) $ageRestricted,
                'genre'          => $genre,
                'theme_id'       => $themeId,
                'created_at'     => time(),
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description', 'age_restricted', 'genre', 'server_theme_id', 'is_active'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `server` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `server` WHERE `id` = :id', ['id' => $id]);
    }

    public function setActive(int $id, bool $active): bool {
        return $this->query(
            'UPDATE `server` SET `is_active` = :active WHERE `id` = :id',
            ['active' => (int) $active, 'id' => $id]
        );
    }

    public function isOwner(int $serverId, int $userId): bool {
        $this->query(
            'SELECT id FROM `server` WHERE `id` = :id AND `created_by` = :uid LIMIT 1',
            ['id' => $serverId, 'uid' => $userId]
        );
        return $this->fetchOne() !== null;
    }

    public function search(string $q, int $limit = 20): array {
        $this->query(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id) AS party_count
             FROM `server` s
             WHERE s.name LIKE :q OR s.description LIKE :q
             ORDER BY s.created_at DESC
             LIMIT :limit',
            ['q' => '%' . $q . '%', 'limit' => $limit]
        );
        return $this->fetchAll();
    }

}
