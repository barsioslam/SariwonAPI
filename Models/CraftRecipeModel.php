<?php

namespace SariwonAPI\Models;

class CraftRecipeModel extends Model {

    public function __construct() {
        parent::__construct('craft_recipe');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT cr.*, i.name AS result_item_name, s.name AS required_skill_name
             FROM `craft_recipe` cr
             JOIN `item` i ON i.id = cr.result_item_id
             LEFT JOIN `skill` s ON s.id = cr.required_skill_id
             WHERE cr.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT cr.*, i.name AS result_item_name, s.name AS required_skill_name
             FROM `craft_recipe` cr
             JOIN `item` i ON i.id = cr.result_item_id
             LEFT JOIN `skill` s ON s.id = cr.required_skill_id
             WHERE cr.server_id = :sid
             ORDER BY i.name',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    // Plusieurs recettes possibles pour un même objet résultant
    public function findByResultItem(int $itemId): array {
        $this->query(
            'SELECT cr.*, s.name AS required_skill_name
             FROM `craft_recipe` cr
             LEFT JOIN `skill` s ON s.id = cr.required_skill_id
             WHERE cr.result_item_id = :iid',
            ['iid' => $itemId]
        );
        return $this->fetchAll();
    }

    public function create(int $serverId, int $resultItemId, int $resultQuantity = 1, int $requiredSkillId = null): int {
        $this->query(
            'INSERT INTO `craft_recipe`
                (`server_id`, `result_item_id`, `result_quantity`, `required_skill_id`)
             VALUES (:sid, :iid, :qty, :skill)',
            ['sid' => $serverId, 'iid' => $resultItemId, 'qty' => $resultQuantity, 'skill' => $requiredSkillId]
        );
        return $this->lastId();
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `craft_recipe` WHERE `id` = :id', ['id' => $id]);
    }

}
