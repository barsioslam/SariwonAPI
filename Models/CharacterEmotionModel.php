<?php

namespace SariwonAPI\Models;

class CharacterEmotionModel extends Model {

    public function __construct() {
        parent::__construct('character_emotion');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT ce.*, e.name AS emotion_name
             FROM `character_emotion` ce
             JOIN `emotion` e ON e.id = ce.emotion_id
             WHERE ce.character_id = :cid
               AND (ce.expires_at IS NULL OR ce.expires_at > NOW())
             ORDER BY ce.intensity DESC, ce.triggered_at DESC',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function add(int $charId, int $emotionId, int $intensity = 1, string $expiresAt = null): int {
        $this->query(
            'INSERT INTO `character_emotion`
                (`character_id`, `emotion_id`, `intensity`, `expires_at`)
             VALUES (:cid, :eid, :intensity, :expires_at)',
            ['cid' => $charId, 'eid' => $emotionId, 'intensity' => $intensity, 'expires_at' => $expiresAt]
        );
        return $this->lastId();
    }

    public function remove(int $id): bool {
        return $this->query('DELETE FROM `character_emotion` WHERE `id` = :id', ['id' => $id]);
    }

    public function clearExpired(): int {
        $this->query('DELETE FROM `character_emotion` WHERE `expires_at` IS NOT NULL AND `expires_at` <= NOW()');
        return $this->rowCount();
    }

}
