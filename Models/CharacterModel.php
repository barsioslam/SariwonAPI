<?php

namespace SariwonAPI\Models;

class CharacterModel extends Model {

    public function __construct() {
        parent::__construct('character');
    }

    public function findById(int $id): ?array {
        // JOIN conditionnel : si ruleset_id est défini → tables ruleset_*, sinon → tables live
        $this->query(
            'SELECT c.*,
                    COALESCE(rs.name,  s.name)  AS species_name,
                    COALESCE(rc.name,  cl.name) AS class_name,
                    COALESCE(rj.name,  j.name)  AS job_name,
                    COALESCE(ra.label, a.label) AS alignment_label,
                    COALESCE(ra.aura,  a.aura)  AS alignment_aura
             FROM `character` c
             LEFT JOIN `species`          s  ON c.ruleset_id IS NULL     AND s.id  = c.species_id
             LEFT JOIN `character_class`  cl ON c.ruleset_id IS NULL     AND cl.id = c.class_id
             LEFT JOIN `character_job`    j  ON c.ruleset_id IS NULL     AND j.id  = c.job_id
             LEFT JOIN `alignment`        a  ON c.ruleset_id IS NULL     AND a.id  = c.alignment_id
             LEFT JOIN `ruleset_species`  rs ON c.ruleset_id IS NOT NULL AND rs.id = c.species_id   AND rs.ruleset_id = c.ruleset_id
             LEFT JOIN `ruleset_class`    rc ON c.ruleset_id IS NOT NULL AND rc.id = c.class_id     AND rc.ruleset_id = c.ruleset_id
             LEFT JOIN `ruleset_job`      rj ON c.ruleset_id IS NOT NULL AND rj.id = c.job_id       AND rj.ruleset_id = c.ruleset_id
             LEFT JOIN `ruleset_alignment` ra ON c.ruleset_id IS NOT NULL AND ra.id = c.alignment_id AND ra.ruleset_id = c.ruleset_id
             WHERE c.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByUser(int $userId): array {
        $this->query(
            'SELECT c.`id`, c.`name`, c.`level`, c.`xp`, c.`server_id`, c.`ruleset_id`,
                    srv.`name` AS server_name,
                    COALESCE(rs.name,  s.name)  AS species_name,
                    COALESCE(rc.name,  cl.name) AS class_name
             FROM `character` c
             LEFT JOIN `server`          srv ON srv.id = c.server_id
             LEFT JOIN `species`          s  ON c.ruleset_id IS NULL     AND s.id  = c.species_id
             LEFT JOIN `character_class`  cl ON c.ruleset_id IS NULL     AND cl.id = c.class_id
             LEFT JOIN `ruleset_species`  rs ON c.ruleset_id IS NOT NULL AND rs.id = c.species_id  AND rs.ruleset_id = c.ruleset_id
             LEFT JOIN `ruleset_class`    rc ON c.ruleset_id IS NOT NULL AND rc.id = c.class_id    AND rc.ruleset_id = c.ruleset_id
             WHERE c.`user_id` = :uid
             ORDER BY c.`server_id`, c.`created_at` DESC',
            ['uid' => $userId]
        );
        return $this->fetchAll();
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT c.`id`, c.`name`, c.`level`, c.`user_id`,
                    COALESCE(rs.name, s.name)  AS species_name,
                    COALESCE(rc.name, cl.name) AS class_name
             FROM `character` c
             LEFT JOIN `species`          s  ON c.ruleset_id IS NULL     AND s.id  = c.species_id
             LEFT JOIN `character_class`  cl ON c.ruleset_id IS NULL     AND cl.id = c.class_id
             LEFT JOIN `ruleset_species`  rs ON c.ruleset_id IS NOT NULL AND rs.id = c.species_id  AND rs.ruleset_id = c.ruleset_id
             LEFT JOIN `ruleset_class`    rc ON c.ruleset_id IS NOT NULL AND rc.id = c.class_id    AND rc.ruleset_id = c.ruleset_id
             WHERE c.`server_id` = :sid
             ORDER BY c.`level` DESC',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function create(int $userId, array $data): int {
        $this->query(
            'INSERT INTO `character`
                (`user_id`, `server_id`, `ruleset_id`, `name`, `age`, `sex_gender`, `species_id`, `class_id`, `job_id`)
             VALUES
                (:user_id, :server_id, :ruleset_id, :name, :age, :sex_gender, :species_id, :class_id, :job_id)',
            [
                'user_id'    => $userId,
                'server_id'  => isset($data['server_id'])  ? (int)    $data['server_id']  : null,
                'ruleset_id' => isset($data['ruleset_id']) ? (int)    $data['ruleset_id'] : null,
                'name'       => trim((string) $data['name']),
                'age'        => isset($data['age'])        ? (int)    $data['age']        : null,
                'sex_gender' => isset($data['sex_gender']) ? (string) $data['sex_gender'] : null,
                'species_id' => isset($data['species_id']) ? (int)    $data['species_id'] : null,
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
