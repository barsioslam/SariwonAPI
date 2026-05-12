<?php

namespace SariwonAPI\Models;

class AlterationModel extends Model {

    public function __construct() {
        parent::__construct('alteration');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `alteration` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findForServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `alteration`
             WHERE `server_id` IS NULL OR `server_id` = :sid
             ORDER BY `type`, `server_id` IS NOT NULL, `name`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(string $name, string $type = 'classic', bool $isNegative = true, string $description = null, int $serverId = null, int $createdBy = null): int {
        $this->query(
            'INSERT INTO `alteration` (`name`, `type`, `is_negative`, `description`, `server_id`, `created_by`)
             VALUES (:name, :type, :is_negative, :description, :server_id, :created_by)',
            [
                'name'        => $name,
                'type'        => $type,
                'is_negative' => (int) $isNegative,
                'description' => $description,
                'server_id'   => $serverId,
                'created_by'  => $createdBy,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'type', 'is_negative', 'description'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `alteration` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `alteration` WHERE `id` = :id', ['id' => $id]);
    }

}
