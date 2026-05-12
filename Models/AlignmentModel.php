<?php

namespace SariwonAPI\Models;

class AlignmentModel extends Model {

    public function __construct() {
        parent::__construct('alignment');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `alignment` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findForServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `alignment`
             WHERE `server_id` IS NULL OR `server_id` = :sid
             ORDER BY `server_id` IS NOT NULL, `label`',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(string $label, string $aura = 'neutral', int $serverId = null, int $createdBy = null): int {
        $this->query(
            'INSERT INTO `alignment` (`label`, `aura`, `server_id`, `created_by`)
             VALUES (:label, :aura, :server_id, :created_by)',
            ['label' => $label, 'aura' => $aura, 'server_id' => $serverId, 'created_by' => $createdBy]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['label', 'aura'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `alignment` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `alignment` WHERE `id` = :id', ['id' => $id]);
    }

}
