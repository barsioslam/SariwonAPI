<?php

namespace SariwonAPI\Models;

class NpcModel extends Model {

    public function __construct() {
        parent::__construct('npc');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT n.*, f.name AS faction_name
             FROM `npc` n
             LEFT JOIN `faction` f ON f.id = n.faction_id
             WHERE n.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT n.*, f.name AS faction_name
             FROM `npc` n
             LEFT JOIN `faction` f ON f.id = n.faction_id
             WHERE n.server_id = :sid
             ORDER BY n.name',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function findByFaction(int $factionId): array {
        $this->query(
            'SELECT * FROM `npc` WHERE `faction_id` = :fid ORDER BY `name`',
            ['fid' => $factionId]
        );
        return $this->fetchAll();
    }

    public function create(int $serverId, string $name, int $factionId = null, int $baseDisposition = 0): int {
        $this->query(
            'INSERT INTO `npc` (`server_id`, `name`, `faction_id`, `base_disposition`)
             VALUES (:sid, :name, :fid, :disp)',
            ['sid' => $serverId, 'name' => $name, 'fid' => $factionId, 'disp' => $baseDisposition]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'faction_id', 'base_disposition'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `npc` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `npc` WHERE `id` = :id', ['id' => $id]);
    }

}
