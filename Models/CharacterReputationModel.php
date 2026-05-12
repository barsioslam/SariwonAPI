<?php

namespace SariwonAPI\Models;

class CharacterReputationModel extends Model {

    public function __construct() {
        parent::__construct('character_reputation');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT cr.*, f.name AS faction_name
             FROM `character_reputation` cr
             JOIN `faction` f ON f.id = cr.faction_id
             WHERE cr.character_id = :cid
             ORDER BY cr.score DESC',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function getScore(int $charId, int $factionId): int {
        $this->query(
            'SELECT `score` FROM `character_reputation`
             WHERE `character_id` = :cid AND `faction_id` = :fid LIMIT 1',
            ['cid' => $charId, 'fid' => $factionId]
        );
        $row = $this->fetchOne();
        return $row ? (int) $row['score'] : 0;
    }

    public function upsert(int $charId, int $factionId, int $score): void {
        $score = max(-100, min(100, $score));
        $this->query(
            'INSERT INTO `character_reputation` (`character_id`, `faction_id`, `score`)
             VALUES (:cid, :fid, :score)
             ON DUPLICATE KEY UPDATE `score` = :score2',
            ['cid' => $charId, 'fid' => $factionId, 'score' => $score, 'score2' => $score]
        );
    }

    // Modifie le score de façon relative (+10, -5, etc.)
    public function adjustScore(int $charId, int $factionId, int $delta): int {
        $current  = $this->getScore($charId, $factionId);
        $newScore = max(-100, min(100, $current + $delta));
        $this->upsert($charId, $factionId, $newScore);
        return $newScore;
    }

}
