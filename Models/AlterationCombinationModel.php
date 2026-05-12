<?php

namespace SariwonAPI\Models;

class AlterationCombinationModel extends Model {

    public function __construct() {
        parent::__construct('alteration_combination');
    }

    public function findById(int $id): ?array {
        $this->query('SELECT * FROM `alteration_combination` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        return $this->fetchOne();
    }

    // Règles effectives pour un serveur : templates non surchargés + règles GM
    public function findForServer(int $serverId): array {
        $this->query(
            'SELECT
                COALESCE(custom.result_id, tpl.result_id) AS result_id,
                tpl.trigger_a_id,
                tpl.trigger_b_id,
                tpl.id AS template_id,
                custom.id AS override_id
             FROM `alteration_combination` tpl
             LEFT JOIN `alteration_combination` custom
                ON custom.parent_id = tpl.id AND custom.server_id = :sid
             WHERE tpl.server_id IS NULL

             UNION ALL

             SELECT result_id, trigger_a_id, trigger_b_id, NULL AS template_id, id AS override_id
             FROM `alteration_combination`
             WHERE server_id = :sid2 AND parent_id IS NULL',
            ['sid' => $serverId, 'sid2' => $serverId]
        );
        return $this->fetchAll();
    }

    // Toutes les règles d'un serveur (templates + surcharges + nouvelles)
    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT * FROM `alteration_combination`
             WHERE `server_id` IS NULL OR `server_id` = :sid
             ORDER BY `server_id` IS NOT NULL',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(int $triggerA, int $triggerB, int $result, int $serverId = null, int $createdBy = null, int $parentId = null): int {
        $this->query(
            'INSERT INTO `alteration_combination`
                (`trigger_a_id`, `trigger_b_id`, `result_id`, `server_id`, `created_by`, `parent_id`)
             VALUES (:a, :b, :result, :server_id, :created_by, :parent_id)',
            [
                'a'          => $triggerA,
                'b'          => $triggerB,
                'result'     => $result,
                'server_id'  => $serverId,
                'created_by' => $createdBy,
                'parent_id'  => $parentId,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, int $resultId): bool {
        return $this->query(
            'UPDATE `alteration_combination` SET `result_id` = :result WHERE `id` = :id',
            ['result' => $resultId, 'id' => $id]
        );
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `alteration_combination` WHERE `id` = :id', ['id' => $id]);
    }

}
