<?php

namespace SariwonAPI\Models;

class CraftIngredientModel extends Model {

    public function __construct() {
        parent::__construct('craft_ingredient');
    }

    public function findByRecipe(int $recipeId): array {
        $this->query(
            'SELECT ci.*, i.name AS item_name, i.weight
             FROM `craft_ingredient` ci
             JOIN `item` i ON i.id = ci.item_id
             WHERE ci.recipe_id = :rid
             ORDER BY i.name',
            ['rid' => $recipeId]
        );
        return $this->fetchAll();
    }

    public function add(int $recipeId, int $itemId, int $quantity = 1): int {
        $this->query(
            'INSERT INTO `craft_ingredient` (`recipe_id`, `item_id`, `quantity`)
             VALUES (:rid, :iid, :qty)',
            ['rid' => $recipeId, 'iid' => $itemId, 'qty' => $quantity]
        );
        return $this->lastId();
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `craft_ingredient` WHERE `id` = :id', ['id' => $id]);
    }

    public function deleteByRecipe(int $recipeId): bool {
        return $this->query('DELETE FROM `craft_ingredient` WHERE `recipe_id` = :rid', ['rid' => $recipeId]);
    }

}
