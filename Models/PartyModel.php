<?php

namespace SariwonAPI\Models;

class PartyModel extends Model {

    public function __construct() {
        parent::__construct('party');
    }

    public function findByServer(int $serverId): array {
        $this->query(
            'SELECT p.*,
                    u.username AS created_by_username,
                    (SELECT COUNT(*) FROM party_member pm WHERE pm.party_id = p.id) AS member_count
             FROM `party` p
             JOIN `user` u ON u.id = p.created_by
             WHERE p.server_id = :server_id
             ORDER BY p.is_active DESC, p.created_at DESC',
            ['server_id' => $serverId]
        );
        return $this->fetchAll();
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT p.*,
                    u.username AS created_by_username,
                    sr.version AS ruleset_version, sr.label AS ruleset_label,
                    (SELECT COUNT(*) FROM party_member pm WHERE pm.party_id = p.id) AS member_count
             FROM `party` p
             JOIN `user` u ON u.id = p.created_by
             LEFT JOIN `server_ruleset` sr ON sr.id = p.ruleset_id
             WHERE p.id = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne() ?: null;
    }

    public function create(
        int     $serverId,
        int     $createdBy,
        string  $name,
        ?string $description,
        bool    $isPublic,
        ?string $inviteCode,
        int     $maxPlayers,
        ?int    $rulesetId
    ): int {
        $this->query(
            'INSERT INTO `party`
                (`server_id`, `created_by`, `name`, `description`, `is_public`,
                 `invite_code`, `max_players`, `ruleset_id`, `is_active`, `created_at`)
             VALUES
                (:server_id, :created_by, :name, :description, :is_public,
                 :invite_code, :max_players, :ruleset_id, 0, :created_at)',
            [
                'server_id'   => $serverId,
                'created_by'  => $createdBy,
                'name'        => $name,
                'description' => $description,
                'is_public'   => $isPublic ? 1 : 0,
                'invite_code' => $inviteCode,
                'max_players' => $maxPlayers,
                'ruleset_id'  => $rulesetId,
                'created_at'  => time(),
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description', 'is_public', 'invite_code', 'max_players', 'ruleset_id'];
        $sets    = [];
        $params  = ['id' => $id];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]       = "`$col` = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        return $this->query(
            'UPDATE `party` SET ' . implode(', ', $sets) . ' WHERE `id` = :id',
            $params
        );
    }

    public function setActive(int $id, bool $active): bool {
        return $this->query(
            'UPDATE `party` SET `is_active` = :active, `started_at` = :started_at WHERE `id` = :id',
            [
                'id'         => $id,
                'active'     => $active ? 1 : 0,
                'started_at' => $active ? time() : null,
            ]
        );
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `party` WHERE `id` = :id', ['id' => $id]);
    }

    public function inviteCodeExists(string $code): bool {
        $this->query(
            'SELECT id FROM `party` WHERE `invite_code` = :code LIMIT 1',
            ['code' => $code]
        );
        return $this->fetchOne() !== null;
    }

    /** Créateur de la partie OU GM du serveur */
    public function canManage(int $partyId, int $userId): bool {
        $this->query(
            'SELECT p.id FROM `party` p
             JOIN `server` s ON s.id = p.server_id
             WHERE p.id = :party_id
               AND (p.created_by = :uid OR s.created_by = :uid2)
             LIMIT 1',
            ['party_id' => $partyId, 'uid' => $userId, 'uid2' => $userId]
        );
        return $this->fetchOne() !== null;
    }

}
