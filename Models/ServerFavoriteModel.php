<?php

namespace SariwonAPI\Models;

class ServerFavoriteModel extends Model {

    public function __construct() {
        parent::__construct('server_favorite');
    }

    public function isFavorited(int $userId, int $serverId): bool {
        $this->query(
            'SELECT id FROM `server_favorite` WHERE `user_id` = :uid AND `server_id` = :sid LIMIT 1',
            ['uid' => $userId, 'sid' => $serverId]
        );
        return $this->fetchOne() !== null;
    }

    public function add(int $userId, int $serverId): void {
        $this->query(
            'INSERT IGNORE INTO `server_favorite` (`user_id`, `server_id`, `saved_at`)
             VALUES (:uid, :sid, :at)',
            ['uid' => $userId, 'sid' => $serverId, 'at' => time()]
        );
    }

    public function remove(int $userId, int $serverId): void {
        $this->query(
            'DELETE FROM `server_favorite` WHERE `user_id` = :uid AND `server_id` = :sid',
            ['uid' => $userId, 'sid' => $serverId]
        );
    }

    public function findByUser(int $userId): array {
        $this->query(
            'SELECT s.*, sf.saved_at,
                    (SELECT COUNT(*) FROM `party` p WHERE p.server_id = s.id) AS party_count
             FROM `server_favorite` sf
             JOIN `server` s ON s.id = sf.server_id
             WHERE sf.user_id = :uid
             ORDER BY sf.saved_at DESC',
            ['uid' => $userId]
        );
        return $this->fetchAll();
    }

}
