<?php

namespace SariwonAPI\Models;

class SkillModel extends Model {

    public function __construct() {
        parent::__construct('skill');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `skill` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findForServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `skill`
             WHERE `server_id` IS NULL OR `server_id` = :sid
             ORDER BY `type`, `server_id` IS NOT NULL, `name`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function findForServerByType(int $serverId, string $type): array {
        $this->query(
            'SELECT * FROM `skill`
             WHERE (`server_id` IS NULL OR `server_id` = :sid) AND `type` = :type
             ORDER BY `server_id` IS NOT NULL, `name`',
            ['sid' => $serverId, 'type' => $type]
        );
        return $this->fetchAll();
    }

    public function create(string $name, string $type, string $description = null, int $serverId = null, int $createdBy = null): int {
        $this->query(
            'INSERT INTO `skill` (`name`, `type`, `description`, `server_id`, `created_by`)
             VALUES (:name, :type, :description, :server_id, :created_by)',
            ['name' => $name, 'type' => $type, 'description' => $description, 'server_id' => $serverId, 'created_by' => $createdBy]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'type', 'description'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `skill` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `skill` WHERE `id` = :id', ['id' => $id]);
    }

}
