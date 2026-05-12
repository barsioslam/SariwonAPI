<?php

namespace SariwonAPI\Models;

class ServerModel extends Model {

    public function __construct() {
        parent::__construct('server');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT s.*, ts.title AS theme_title
             FROM `server` s
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             WHERE s.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByUser(int $userId): array {
        $this->query(
            'SELECT s.*, sm.role, sm.joined_at, ts.title AS theme_title
             FROM `server_member` sm
             JOIN `server` s ON s.id = sm.server_id
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             WHERE sm.user_id = :user_id
             ORDER BY sm.joined_at DESC',
            ['user_id' => $userId]
        );
        return $this->fetchAll();
    }

    public function findPublic(): array {
        $this->query(
            'SELECT s.*, ts.title AS theme_title,
                    (SELECT COUNT(*) FROM `server_member` WHERE server_id = s.id) AS member_count
             FROM `server` s
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             WHERE s.visibility = \'public\'
             ORDER BY s.created_at DESC'
        );
        return $this->fetchAll();
    }

    public function findByInviteCode(string $code): ?array {
        $this->query(
            'SELECT * FROM `server` WHERE `invite_code` = :code LIMIT 1',
            ['code' => $code]
        );
        return $this->fetchOne();
    }

    public function inviteCodeExists(string $code): bool {
        $this->query(
            'SELECT id FROM `server` WHERE `invite_code` = :code LIMIT 1',
            ['code' => $code]
        );
        return $this->fetchOne() !== null;
    }

    public function create(
        int    $createdBy,
        string $name,
        string $description,
        string $visibility,
        int    $maxPlayers,
        ?string $inviteCode,
        bool   $ageRestricted,
        ?string $genre,
        ?int   $themeId = null
    ): int {
        $this->query(
            'INSERT INTO `server`
                (`created_by`, `name`, `description`, `visibility`, `max_players`,
                 `invite_code`, `age_restricted`, `genre`, `server_theme_id`, `created_at`)
             VALUES
                (:created_by, :name, :description, :visibility, :max_players,
                 :invite_code, :age_restricted, :genre, :theme_id, :created_at)',
            [
                'created_by'    => $createdBy,
                'name'          => $name,
                'description'   => $description,
                'visibility'    => $visibility,
                'max_players'   => $maxPlayers,
                'invite_code'   => $inviteCode,
                'age_restricted'=> (int) $ageRestricted,
                'genre'         => $genre,
                'theme_id'      => $themeId,
                'created_at'    => time(),
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description', 'visibility', 'max_players', 'invite_code', 'age_restricted', 'genre', 'server_theme_id'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `server` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `server` WHERE `id` = :id', ['id' => $id]);
    }

    public function isOwner(int $serverId, int $userId): bool {
        $this->query(
            'SELECT id FROM `server` WHERE `id` = :id AND `created_by` = :user_id LIMIT 1',
            ['id' => $serverId, 'user_id' => $userId]
        );
        return $this->fetchOne() !== null;
    }

}
