<?php

namespace SariwonAPI\Models;

class ServerMemberModel extends Model {

    public function __construct() {
        parent::__construct('server_member');
    }

    public function isMember(int $serverId, int $userId): bool {
        $this->query(
            'SELECT id FROM `server_member` WHERE `server_id` = :server_id AND `user_id` = :user_id LIMIT 1',
            ['server_id' => $serverId, 'user_id' => $userId]
        );
        return $this->fetchOne() !== null;
    }

    public function add(int $serverId, int $userId, string $role = 'player'): int {
        $this->query(
            'INSERT INTO `server_member` (`server_id`, `user_id`, `role`, `joined_at`)
             VALUES (:server_id, :user_id, :role, NOW())',
            ['server_id' => $serverId, 'user_id' => $userId, 'role' => $role]
        );
        return $this->lastId();
    }

    public function remove(int $serverId, int $userId): bool {
        return $this->query(
            'DELETE FROM `server_member` WHERE `server_id` = :server_id AND `user_id` = :user_id',
            ['server_id' => $serverId, 'user_id' => $userId]
        );
    }

    public function countMembers(int $serverId): int {
        $this->query(
            'SELECT COUNT(*) AS total FROM `server_member` WHERE `server_id` = :server_id',
            ['server_id' => $serverId]
        );
        $row = $this->fetchOne();
        return (int) ($row['total'] ?? 0);
    }

}
