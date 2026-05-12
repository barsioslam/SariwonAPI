<?php

namespace SariwonAPI\Models;

class CharacterAlterationModel extends Model {

    public function __construct() {
        parent::__construct('character_alteration');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT ca.*, a.name AS alteration_name, a.type AS alteration_type, a.is_negative
             FROM `character_alteration` ca
             JOIN `alteration` a ON a.id = ca.alteration_id
             WHERE ca.character_id = :cid
               AND (ca.expires_at IS NULL OR ca.expires_at > NOW())
             ORDER BY ca.applied_at DESC',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function add(int $charId, int $alterationId, int $severity = 1, string $source = null, string $expiresAt = null): int {
        $this->query(
            'INSERT INTO `character_alteration`
                (`character_id`, `alteration_id`, `severity`, `source`, `expires_at`)
             VALUES (:cid, :alt, :severity, :source, :expires_at)',
            [
                'cid'        => $charId,
                'alt'        => $alterationId,
                'severity'   => $severity,
                'source'     => $source,
                'expires_at' => $expiresAt,
            ]
        );
        return $this->lastId();
    }

    public function remove(int $id): bool {
        return $this->query('DELETE FROM `character_alteration` WHERE `id` = :id', ['id' => $id]);
    }

    public function clearExpired(): int {
        $this->query('DELETE FROM `character_alteration` WHERE `expires_at` IS NOT NULL AND `expires_at` <= NOW()');
        return $this->rowCount();
    }

}
