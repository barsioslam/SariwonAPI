<?php

namespace SariwonAPI\Models;

class CharacterMalusModel extends Model {

    public function __construct() {
        parent::__construct('character_malus');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT * FROM `character_malus`
             WHERE `character_id` = :cid
               AND (`expires_at` IS NULL OR `expires_at` > NOW())
             ORDER BY `applied_at` DESC',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function add(int $charId, string $label, string $stat, int $modifier, string $source = null, string $expiresAt = null): int {
        $this->query(
            'INSERT INTO `character_malus`
                (`character_id`, `label`, `stat`, `modifier`, `source`, `expires_at`)
             VALUES (:cid, :label, :stat, :modifier, :source, :expires_at)',
            [
                'cid'        => $charId,
                'label'      => $label,
                'stat'       => $stat,
                'modifier'   => $modifier,
                'source'     => $source,
                'expires_at' => $expiresAt,
            ]
        );
        return $this->lastId();
    }

    public function remove(int $id): bool {
        return $this->query('DELETE FROM `character_malus` WHERE `id` = :id', ['id' => $id]);
    }

    public function clearExpired(): int {
        $this->query('DELETE FROM `character_malus` WHERE `expires_at` IS NOT NULL AND `expires_at` <= NOW()');
        return $this->rowCount();
    }

}
