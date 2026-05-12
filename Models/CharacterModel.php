<?php

namespace SariwonAPI\Models;

class CharacterModel extends Model {

    public function __construct() {
        parent::__construct('character');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT c.*,
                    s.name  AS species_name,
                    cl.name AS class_name,
                    j.name  AS job_name,
                    a.label AS alignment_label,
                    a.aura  AS alignment_aura
             FROM `character` c
             LEFT JOIN `species`         s  ON s.id  = c.species_id
             LEFT JOIN `character_class` cl ON cl.id = c.class_id
             LEFT JOIN `character_job`   j  ON j.id  = c.job_id
             LEFT JOIN `alignment`       a  ON a.id  = c.alignment_id
             WHERE c.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByUser(int $userId): array {
        $this->query(
            'SELECT c.`id`, c.`name`, c.`level`, c.`xp`, c.`server_id`,
                    s.name AS species_name, cl.name AS class_name
             FROM `character` c
             LEFT JOIN `species`         s  ON s.id  = c.species_id
             LEFT JOIN `character_class` cl ON cl.id = c.class_id
             WHERE c.`user_id` = :uid
             ORDER BY c.`created_at` DESC',
            ['uid' => $userId]
        );
        return $this->fetchAll();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT c.`id`, c.`name`, c.`level`, c.`user_id`,
                    s.name AS species_name, cl.name AS class_name
             FROM `character` c
             LEFT JOIN `species`         s  ON s.id  = c.species_id
             LEFT JOIN `character_class` cl ON cl.id = c.class_id
             WHERE c.`server_id` = :sid
             ORDER BY c.`level` DESC',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(int $userId, array $data): int {
        $this->query(
            'INSERT INTO `character`
                (`user_id`, `server_id`, `name`, `age`, `sex_gender`, `species_id`, `class_id`, `job_id`)
             VALUES
                (:user_id, :server_id, :name, :age, :sex_gender, :species_id, :class_id, :job_id)',
            [
                'user_id'    => $userId,
                'server_id'  => (int)   $data['server_id'],
                'name'       => trim((string) $data['name']),
                'age'        => isset($data['age'])        ? (int)    $data['age']        : null,
                'sex_gender' => isset($data['sex_gender']) ? (string) $data['sex_gender'] : null,
                'species_id' => (int)   $data['species_id'],
                'class_id'   => isset($data['class_id'])   ? (int)    $data['class_id']   : null,
                'job_id'     => isset($data['job_id'])      ? (int)    $data['job_id']     : null,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = [
            'name', 'age', 'sex_gender', 'history', 'species_id', 'class_id', 'job_id',
            'alignment_id', 'karma_score', 'reputation_global', 'xp', 'level',
            'hp_current', 'hp_max', 'moral_current', 'moral_max',
            'energy_current', 'energy_max', 'hunger', 'thirst',
            'stat_str', 'stat_dex', 'stat_con', 'stat_int', 'stat_wis', 'stat_cha',
            'carry_capacity',
        ];

        $fields = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `character` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `character` WHERE `id` = :id', ['id' => $id]);
    }

    public function belongsToUser(int $charId, int $userId): bool {
        $this->query(
            'SELECT `id` FROM `character` WHERE `id` = :id AND `user_id` = :uid LIMIT 1',
            ['id' => $charId, 'uid' => $userId]
        );
        return $this->rowCount() > 0;
    }

}
