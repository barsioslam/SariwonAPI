<?php

namespace SariwonAPI\Models;

class ItemModel extends Model {

    public function __construct() {
        parent::__construct('item');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `item` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `item` WHERE `server_id` = :sid ORDER BY `type`, `name`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function findByServerAndType(int $serverId, string $type): array {
        $this->query(
            'SELECT * FROM `item` WHERE `server_id` = :sid AND `type` = :type ORDER BY `name`',
            ['sid' => $serverId, 'type' => $type]
        );
        return $this->fetchAll();
    }

    public function create(array $data): int {
        $allowed = ['server_id', 'name', 'description', 'type', 'weight', 'durability_max', 'is_craftable'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        $cols = implode(', ', array_map(fn(string $k): string => "`$k`", array_keys($fields)));
        $vals = implode(', ', array_map(fn(string $k): string => ":$k", array_keys($fields)));

        $this->query("INSERT INTO `item` ($cols) VALUES ($vals)", $fields);
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description', 'type', 'weight', 'durability_max', 'is_craftable'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `item` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `item` WHERE `id` = :id', ['id' => $id]);
    }

}
