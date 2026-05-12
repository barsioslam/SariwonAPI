<?php

namespace SariwonAPI\Models;

class CharacterSkillModel extends Model {

    public function __construct() {
        parent::__construct('character_skill');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT cs.*, s.name AS skill_name, s.type AS skill_type
             FROM `character_skill` cs
             JOIN `skill` s ON s.id = cs.skill_id
             WHERE cs.character_id = :cid
             ORDER BY s.type, s.name',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function upsert(int $charId, int $skillId, int $value): void {
        $this->query(
            'INSERT INTO `character_skill` (`character_id`, `skill_id`, `value`)
             VALUES (:cid, :sid, :val)
             ON DUPLICATE KEY UPDATE `value` = :val2',
            ['cid' => $charId, 'sid' => $skillId, 'val' => $value, 'val2' => $value]
        );
    }

    public function delete(int $charId, int $skillId): bool {
        return $this->query(
            'DELETE FROM `character_skill` WHERE `character_id` = :cid AND `skill_id` = :sid',
            ['cid' => $charId, 'sid' => $skillId]
        );
    }

    public function deleteAllByCharacter(int $charId): bool {
        return $this->query(
            'DELETE FROM `character_skill` WHERE `character_id` = :cid',
            ['cid' => $charId]
        );
    }

}
