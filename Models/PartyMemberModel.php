<?php

namespace SariwonAPI\Models;

class PartyMemberModel extends Model {

    public function __construct() {
        parent::__construct('party_member');
    }

    public function isMember(int $partyId, int $userId): bool {
        $this->query(
            'SELECT id FROM `party_member` WHERE `party_id` = :party_id AND `user_id` = :user_id LIMIT 1',
            ['party_id' => $partyId, 'user_id' => $userId]
        );
        return $this->fetchOne() !== null;
    }

    public function add(int $partyId, int $userId, ?int $characterId = null): int {
        $this->query(
            'INSERT INTO `party_member` (`party_id`, `user_id`, `character_id`, `joined_at`)
             VALUES (:party_id, :user_id, :character_id, :joined_at)',
            [
                'party_id'     => $partyId,
                'user_id'      => $userId,
                'character_id' => $characterId,
                'joined_at'    => time(),
            ]
        );
        return $this->lastId();
    }

    public function remove(int $partyId, int $userId): bool {
        return $this->query(
            'DELETE FROM `party_member` WHERE `party_id` = :party_id AND `user_id` = :user_id',
            ['party_id' => $partyId, 'user_id' => $userId]
        );
    }

    public function setCharacter(int $partyId, int $userId, ?int $characterId): bool {
        return $this->query(
            'UPDATE `party_member` SET `character_id` = :character_id
             WHERE `party_id` = :party_id AND `user_id` = :user_id',
            ['party_id' => $partyId, 'user_id' => $userId, 'character_id' => $characterId]
        );
    }

    public function countMembers(int $partyId): int {
        $this->query(
            'SELECT COUNT(*) AS total FROM `party_member` WHERE `party_id` = :party_id',
            ['party_id' => $partyId]
        );
        $row = $this->fetchOne();
        return (int) ($row['total'] ?? 0);
    }

    public function getMembers(int $partyId): array {
        $this->query(
            'SELECT pm.joined_at, pm.character_id,
                    u.id AS user_id, u.username,
                    c.name AS character_name, c.level AS character_level
             FROM `party_member` pm
             JOIN `user` u ON u.id = pm.user_id
             LEFT JOIN `character` c ON c.id = pm.character_id
             WHERE pm.party_id = :party_id
             ORDER BY pm.joined_at ASC',
            ['party_id' => $partyId]
        );
        return $this->fetchAll();
    }

}
