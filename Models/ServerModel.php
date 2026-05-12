<?php

namespace SariwonAPI\Models;

class ServerModel extends Model {

    public function __construct() {
        parent::__construct('server');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT s.*, ts.title AS theme_title
             FROM `server` s
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             WHERE s.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findAll(): array {
        $this->query(
            'SELECT s.*, ts.title AS theme_title
             FROM `server` s
             LEFT JOIN `tag_style` ts ON ts.id = s.server_theme_id
             ORDER BY s.id'
        );
        return $this->fetchAll();
    }

    public function create(string $name, string $description, int $themeId): int {
        $this->query(
            'INSERT INTO `server` (`server_name`, `server_description`, `server_theme_id`)
             VALUES (:name, :description, :theme)',
            ['name' => $name, 'description' => $description, 'theme' => $themeId]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['server_name', 'server_description', 'server_theme_id'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `server` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `server` WHERE `id` = :id', ['id' => $id]);
    }

}
