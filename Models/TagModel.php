<?php

namespace SariwonAPI\Models;

class TagModel extends Model {

    public function __construct() {
        parent::__construct('tag');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT t.*, ts.title AS style_title
             FROM `tag` t
             LEFT JOIN `tag_style` ts ON ts.id = t.tag_style_id
             WHERE t.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT t.*, ts.title AS style_title
             FROM `tag` t
             LEFT JOIN `tag_style` ts ON ts.id = t.tag_style_id
             WHERE t.server_id = :sid
             ORDER BY t.tag_content',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(array $data): int {
        $allowed = ['server_id', 'tag_content', 'tag_short', 'tag_icon', 'tag_background', 'tag_accent', 'tag_style_id'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        $cols = implode(', ', array_map(fn(string $k): string => "`$k`", array_keys($fields)));
        $vals = implode(', ', array_map(fn(string $k): string => ":$k", array_keys($fields)));

        $this->query("INSERT INTO `tag` ($cols) VALUES ($vals)", $fields);
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['tag_content', 'tag_short', 'tag_icon', 'tag_background', 'tag_accent', 'tag_style_id'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `tag` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `tag` WHERE `id` = :id', ['id' => $id]);
    }

}
