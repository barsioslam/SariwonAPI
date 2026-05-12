<?php

namespace SariwonAPI\Models;

class NpcMemoryModel extends Model {

    public function __construct() {
        parent::__construct('npc_memory');
    }

    public function findByNpc(int $npcId): array {
        $this->query(
            'SELECT nm.*, c.name AS character_name
             FROM `npc_memory` nm
             JOIN `character` c ON c.id = nm.character_id
             WHERE nm.npc_id = :nid
             ORDER BY nm.created_at DESC',
            ['nid' => $npcId]
        );
        return $this->fetchAll();
    }

    public function findByNpcAndCharacter(int $npcId, int $charId): array {
        $this->query(
            'SELECT * FROM `npc_memory`
             WHERE `npc_id` = :nid AND `character_id` = :cid
             ORDER BY `created_at` DESC',
            ['nid' => $npcId, 'cid' => $charId]
        );
        return $this->fetchAll();
    }

    // Impact cumulé de toutes les mémoires sur la disposition du PNJ envers le personnage
    public function getDispositionImpact(int $npcId, int $charId): int {
        $this->query(
            'SELECT COALESCE(SUM(`impact`), 0) AS total
             FROM `npc_memory`
             WHERE `npc_id` = :nid AND `character_id` = :cid',
            ['nid' => $npcId, 'cid' => $charId]
        );
        $row = $this->fetchOne();
        return (int) ($row['total'] ?? 0);
    }

    public function add(int $npcId, int $charId, string $eventType, int $impact = 0, string $detail = null): int {
        $this->query(
            'INSERT INTO `npc_memory` (`npc_id`, `character_id`, `event_type`, `impact`, `detail`)
             VALUES (:nid, :cid, :event, :impact, :detail)',
            ['nid' => $npcId, 'cid' => $charId, 'event' => $eventType, 'impact' => $impact, 'detail' => $detail]
        );
        return $this->lastId();
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `npc_memory` WHERE `id` = :id', ['id' => $id]);
    }

    public function deleteByNpcAndCharacter(int $npcId, int $charId): bool {
        return $this->query(
            'DELETE FROM `npc_memory` WHERE `npc_id` = :nid AND `character_id` = :cid',
            ['nid' => $npcId, 'cid' => $charId]
        );
    }

}
