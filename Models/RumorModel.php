<?php

namespace SariwonAPI\Models;

class RumorModel extends Model {

    public function __construct() {
        parent::__construct('rumor');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT * FROM `rumor`
             WHERE `about_character_id` = :cid
               AND (`expires_at` IS NULL OR `expires_at` > NOW())
             ORDER BY `spread_level` DESC, `created_at` DESC',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT r.*, c.name AS character_name
             FROM `rumor` r
             JOIN `character` c ON c.id = r.about_character_id
             WHERE r.server_id = :sid
               AND (r.expires_at IS NULL OR r.expires_at > NOW())
             ORDER BY r.spread_level DESC, r.created_at DESC',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(int $serverId, int $aboutCharId, string $content, int $spreadLevel = 1, bool $isTrue = true, string $expiresAt = null): int {
        $this->query(
            'INSERT INTO `rumor`
                (`server_id`, `about_character_id`, `content`, `spread_level`, `is_true`, `expires_at`)
             VALUES (:sid, :cid, :content, :spread, :is_true, :expires_at)',
            [
                'sid'        => $serverId,
                'cid'        => $aboutCharId,
                'content'    => $content,
                'spread'     => $spreadLevel,
                'is_true'    => (int) $isTrue,
                'expires_at' => $expiresAt,
            ]
        );
        return $this->lastId();
    }

    public function updateSpread(int $id, int $spreadLevel): bool {
        return $this->query(
            'UPDATE `rumor` SET `spread_level` = :spread WHERE `id` = :id',
            ['spread' => $spreadLevel, 'id' => $id]
        );
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `rumor` WHERE `id` = :id', ['id' => $id]);
    }

    public function clearExpired(): int {
        $this->query('DELETE FROM `rumor` WHERE `expires_at` IS NOT NULL AND `expires_at` <= NOW()');
        return $this->rowCount();
    }

}
