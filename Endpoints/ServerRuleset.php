<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\ServerModel;
use SariwonAPI\Models\ServerRulesetModel;
use SariwonAPI\Config\ErrorCodes;

class ServerRuleset extends AbstractEndpoint {

    private function requireGm(int $serverId): int {
        $userId = $this->requireAuth();
        if (!(new ServerModel())->isOwner($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }
        return $userId;
    }

    private function getServerOrFail(int $serverId): array {
        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        return $server;
    }

    // ── Liste des versions publiées d'un serveur ─────────────────────────────

    #[Endpoint('get', '/server/{id}/rulesets', 'List server rulesets')]
    public function listRulesets(string $id): array {
        $this->requireAuth();
        $serverId = (int) $id;
        $this->getServerOrFail($serverId);
        return (new ServerRulesetModel())->findByServer($serverId);
    }

    // ── Publier une nouvelle version ─────────────────────────────────────────

    #[Endpoint('post', '/server/{id}/rulesets/publish', 'Publish a new ruleset version')]
    public function publish(string $id): array {
        $serverId = (int) $id;
        $userId   = $this->requireGm($serverId);

        $this->getServerOrFail($serverId);

        $body      = $this->body();
        $label     = trim((string) ($body['label'] ?? '')) ?: null;

        $model     = new ServerRulesetModel();
        $rulesetId = $model->publish($serverId, $userId, $label);

        $ruleset           = $model->findById($rulesetId);
        $ruleset['lookups'] = $model->getLookups($rulesetId);

        return $ruleset;
    }

    // ── Détail d'un ruleset avec ses lookups figés ───────────────────────────

    #[Endpoint('get', '/ruleset/{id}', 'Get ruleset with lookups')]
    public function getRuleset(string $id): array {
        $this->requireAuth();
        $rulesetId = (int) $id;

        $model   = new ServerRulesetModel();
        $ruleset = $model->findById($rulesetId);
        if (!$ruleset) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);

        $ruleset['lookups'] = $model->getLookups($rulesetId);
        return $ruleset;
    }

}
