<?php

namespace SariwonAPI\Models;

class FactionModel extends Model {

    public function __construct() {
        parent::__construct('faction');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `faction` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `faction` WHERE `server_id` = :sid ORDER BY `name`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(int $serverId, string $name, string $description = null): int {
        $this->query(
            'INSERT INTO `faction` (`server_id`, `name`, `description`)
             VALUES (:sid, :name, :description)',
            ['sid' => $serverId, 'name' => $name, 'description' => $description]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `faction` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `faction` WHERE `id` = :id', ['id' => $id]);
    }

}
