<?php

namespace SariwonAPI\Models;

class EmotionModifierModel extends Model {

    public function __construct() {
        parent::__construct('emotion_modifier');
    }

    public function findByEmotion(int $emotionId): array {
        $this->query(
            'SELECT * FROM `emotion_modifier` WHERE `emotion_id` = :eid ORDER BY `stat`',
            ['eid' => $emotionId]
        );
        return $this->fetchAll();
    }

    public function create(int $emotionId, string $stat, int $modifier): int {
        $this->query(
            'INSERT INTO `emotion_modifier` (`emotion_id`, `stat`, `modifier`)
             VALUES (:emotion_id, :stat, :modifier)',
            ['emotion_id' => $emotionId, 'stat' => $stat, 'modifier' => $modifier]
        );
        return $this->lastId();
    }

    public function update(int $id, int $modifier): bool {
        return $this->query(
            'UPDATE `emotion_modifier` SET `modifier` = :modifier WHERE `id` = :id',
            ['modifier' => $modifier, 'id' => $id]
        );
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `emotion_modifier` WHERE `id` = :id', ['id' => $id]);
    }

    public function deleteByEmotion(int $emotionId): bool {
        return $this->query('DELETE FROM `emotion_modifier` WHERE `emotion_id` = :eid', ['eid' => $emotionId]);
    }

}
