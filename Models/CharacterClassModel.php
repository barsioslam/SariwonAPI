<?php

namespace SariwonAPI\Models;

class CharacterClassModel extends Model {

    public function __construct() {
        parent::__construct('character_class');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `character_class` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findForServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `character_class`
             WHERE `server_id` IS NULL OR `server_id` = :sid
             ORDER BY `server_id` IS NOT NULL, `name`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(string $name, string $description = null, int $serverId = null, int $createdBy = null): int {
        $this->query(
            'INSERT INTO `character_class` (`name`, `description`, `server_id`, `created_by`)
             VALUES (:name, :description, :server_id, :created_by)',
            ['name' => $name, 'description' => $description, 'server_id' => $serverId, 'created_by' => $createdBy]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `character_class` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `character_class` WHERE `id` = :id', ['id' => $id]);
    }

}
