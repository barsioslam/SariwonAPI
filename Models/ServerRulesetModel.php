<?php

namespace SariwonAPI\Models;

class ServerRulesetModel extends Model {

    public function __construct() {
        parent::__construct('server_ruleset');
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT sr.*, u.username AS created_by_username
             FROM `server_ruleset` sr
             JOIN `user` u ON u.id = sr.created_by
             WHERE sr.server_id = :sid
             ORDER BY sr.version DESC',
            ['sid' => $serverId]
        );
        return $this->fetchAll();
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT sr.*, u.username AS created_by_username
             FROM `server_ruleset` sr
             JOIN `user` u ON u.id = sr.created_by
             WHERE sr.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne() ?: null;
    }

    public function getLatestVersion(int $serverId): int {
        $this->query(
            'SELECT MAX(version) AS v FROM `server_ruleset` WHERE server_id = :sid',
            ['sid' => $serverId]
        );
        $row = $this->fetchOne();
        return (int) ($row['v'] ?? 0);
    }

    public function publish(int $serverId, int $userId, ?string $label): int {
        $nextVersion = $this->getLatestVersion($serverId) + 1;

        $this->query(
            'INSERT INTO `server_ruleset` (`server_id`, `version`, `label`, `created_by`, `created_at`)
             VALUES (:sid, :version, :label, :uid, :at)',
            ['sid' => $serverId, 'version' => $nextVersion, 'label' => $label, 'uid' => $userId, 'at' => time()]
        );
        $rulesetId = $this->lastId();

        // Snapshot des lookups live du serveur
        $this->snapshotSpecies($rulesetId, $serverId);
        $this->snapshotClasses($rulesetId, $serverId);
        $this->snapshotJobs($rulesetId, $serverId);
        $this->snapshotAlignments($rulesetId, $serverId);

        return $rulesetId;
    }

    private function snapshotSpecies(int $rulesetId, int $serverId): void {
        $this->query(
            'INSERT INTO `ruleset_species` (`ruleset_id`, `name`, `description`)
             SELECT :rid, `name`, `description` FROM `species`
             WHERE `server_id` = :sid OR `server_id` IS NULL',
            ['rid' => $rulesetId, 'sid' => $serverId]
        );
    }

    private function snapshotClasses(int $rulesetId, int $serverId): void {
        $this->query(
            'INSERT INTO `ruleset_class` (`ruleset_id`, `name`, `description`)
             SELECT :rid, `name`, `description` FROM `character_class`
             WHERE `server_id` = :sid OR `server_id` IS NULL',
            ['rid' => $rulesetId, 'sid' => $serverId]
        );
    }

    private function snapshotJobs(int $rulesetId, int $serverId): void {
        $this->query(
            'INSERT INTO `ruleset_job` (`ruleset_id`, `name`, `description`)
             SELECT :rid, `name`, `description` FROM `character_job`
             WHERE `server_id` = :sid OR `server_id` IS NULL',
            ['rid' => $rulesetId, 'sid' => $serverId]
        );
    }

    private function snapshotAlignments(int $rulesetId, int $serverId): void {
        $this->query(
            'INSERT INTO `ruleset_alignment` (`ruleset_id`, `label`, `description`, `aura`)
             SELECT :rid, `label`, `description`, `aura` FROM `alignment`
             WHERE `server_id` = :sid OR `server_id` IS NULL',
            ['rid' => $rulesetId, 'sid' => $serverId]
        );
    }

    public function getLookups(int $rulesetId): array {
        $this->query('SELECT * FROM `ruleset_species`   WHERE ruleset_id = :rid ORDER BY name',  ['rid' => $rulesetId]);
        $species = $this->fetchAll();

        $this->query('SELECT * FROM `ruleset_class`     WHERE ruleset_id = :rid ORDER BY name',  ['rid' => $rulesetId]);
        $classes = $this->fetchAll();

        $this->query('SELECT * FROM `ruleset_job`       WHERE ruleset_id = :rid ORDER BY name',  ['rid' => $rulesetId]);
        $jobs = $this->fetchAll();

        $this->query('SELECT * FROM `ruleset_alignment` WHERE ruleset_id = :rid ORDER BY label', ['rid' => $rulesetId]);
        $alignments = $this->fetchAll();

        return compact('species', 'classes', 'jobs', 'alignments');
    }

}
