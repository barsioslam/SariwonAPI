<?php

namespace SariwonAPI\Models;

class TagStyleModel extends Model {

    public function __construct() {
        parent::__construct('tag_style');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `tag_style` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    public function findAll(): array {
        $this->query('SELECT * FROM `tag_style` ORDER BY `title`');
        return $this->fetchAll();
    }

    public function create(array $data): int {
        $allowed = ['title', 'background_image', 'filter', 'filter_value', 'animation_name'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        $cols = implode(', ', array_map(fn(string $k): string => "`$k`", array_keys($fields)));
        $vals = implode(', ', array_map(fn(string $k): string => ":$k", array_keys($fields)));

        $this->query("INSERT INTO `tag_style` ($cols) VALUES ($vals)", $fields);
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['title', 'background_image', 'filter', 'filter_value', 'animation_name'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `tag_style` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `tag_style` WHERE `id` = :id', ['id' => $id]);
    }

}
