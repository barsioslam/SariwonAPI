<?php

namespace SariwonAPI\Models;

class CharacterInventoryModel extends Model {

    public function __construct() {
        parent::__construct('character_inventory');
    }

    public function findByCharacter(int $charId): array {
        $this->query(
            'SELECT ci.*, i.name AS item_name, i.type AS item_type,
                    i.weight, i.durability_max, i.is_craftable
             FROM `character_inventory` ci
             JOIN `item` i ON i.id = ci.item_id
             WHERE ci.character_id = :cid
             ORDER BY i.type, i.name',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function findEquipped(int $charId): array {
        $this->query(
            'SELECT ci.*, i.name AS item_name, i.type AS item_type, i.weight
             FROM `character_inventory` ci
             JOIN `item` i ON i.id = ci.item_id
             WHERE ci.character_id = :cid AND ci.is_equipped = 1',
            ['cid' => $charId]
        );
        return $this->fetchAll();
    }

    public function addItem(int $charId, int $itemId, int $quantity = 1, int $durability = null): int {
        $this->query(
            'INSERT INTO `character_inventory`
                (`character_id`, `item_id`, `quantity`, `durability_current`)
             VALUES (:cid, :iid, :qty, :dur)',
            ['cid' => $charId, 'iid' => $itemId, 'qty' => $quantity, 'dur' => $durability]
        );
        return $this->lastId();
    }

    public function updateItem(int $id, array $data): bool {
        $allowed = ['quantity', 'durability_current', 'is_equipped'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `character_inventory` SET $sets WHERE `id` = :id", $fields);
    }

    public function removeItem(int $id): bool {
        return $this->query('DELETE FROM `character_inventory` WHERE `id` = :id', ['id' => $id]);
    }

    // Poids total porté par le personnage
    public function getTotalWeight(int $charId): float {
        $this->query(
            'SELECT COALESCE(SUM(i.weight * ci.quantity), 0) AS total
             FROM `character_inventory` ci
             JOIN `item` i ON i.id = ci.item_id
             WHERE ci.character_id = :cid',
            ['cid' => $charId]
        );
        $row = $this->fetchOne();
        return (float) ($row['total'] ?? 0);
    }

}
