<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\PartyModel;
use SariwonAPI\Models\PartyMemberModel;
use SariwonAPI\Models\ServerModel;
use SariwonAPI\Models\ServerRulesetModel;
use SariwonAPI\Models\CharacterModel;
use SariwonAPI\Config\ErrorCodes;

class Party extends AbstractEndpoint {

    private function getPartyOrFail(int $partyId): array {
        $party = (new PartyModel())->findById($partyId);
        if (!$party) jsonResponse(false, [], ErrorCodes::PARTY_NOT_FOUND, 404);
        return $party;
    }

    private function requireCanManage(int $partyId, int $userId): void {
        if (!(new PartyModel())->canManage($partyId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }
    }

    // ── Lister les parties d'un serveur ──────────────────────────────────────

    #[Endpoint('get', '/server/{id}/parties', 'List server parties')]
    public function listParties(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        $parties     = (new PartyModel())->findByServer($serverId);
        $memberModel = new PartyMemberModel();

        foreach ($parties as &$party) {
            $party['is_member']  = $memberModel->isMember((int) $party['id'], $userId);
            $party['can_manage'] = (new PartyModel())->canManage((int) $party['id'], $userId);
            // Masquer le code d'invitation si non-membre d'une partie privée
            if (!$party['is_public'] && !$party['is_member'] && !$party['can_manage']) {
                unset($party['invite_code']);
            }
        }

        return $parties;
    }

    // ── Créer une partie ─────────────────────────────────────────────────────

    #[Endpoint('post', '/server/{id}/parties', 'Create a party')]
    public function createParty(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        $body        = $this->body();
        $name        = trim((string) ($body['name']        ?? ''));
        $description = trim((string) ($body['description'] ?? '')) ?: null;
        $isPublic    = (bool) ($body['is_public']  ?? true);
        $maxPlayers  = max(2, min(30, (int) ($body['max_players'] ?? 8)));
        $rulesetId   = isset($body['ruleset_id'])   ? (int) $body['ruleset_id']   : null;
        $inviteCode  = trim(strtoupper((string) ($body['invite_code'] ?? ''))) ?: null;

        if ($name === '' || strlen($name) > 100) {
            jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);
        }

        // Valider le ruleset si fourni
        if ($rulesetId !== null) {
            $ruleset = (new ServerRulesetModel())->findById($rulesetId);
            if (!$ruleset || (int) $ruleset['server_id'] !== $serverId) {
                jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
            }
        }

        // Générer ou valider le code d'invitation pour partie privée
        if (!$isPublic) {
            $partyModel = new PartyModel();
            if ($inviteCode !== null) {
                if (strlen($inviteCode) > 16 || !preg_match('/^[A-Z0-9\-]+$/', $inviteCode)) {
                    jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
                }
                if ($partyModel->inviteCodeExists($inviteCode)) {
                    jsonResponse(false, [], ErrorCodes::SERVER_INVITE_TAKEN, 409);
                }
            } else {
                do {
                    $inviteCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                } while ($partyModel->inviteCodeExists($inviteCode));
            }
        } else {
            $inviteCode = null;
        }

        $partyModel = new PartyModel();
        $partyId    = $partyModel->create($serverId, $userId, $name, $description, $isPublic, $inviteCode, $maxPlayers, $rulesetId);

        if (!$partyId) jsonResponse(false, [], ErrorCodes::INTERNAL_SERVER_ERROR, 500);

        // Créateur rejoint automatiquement
        (new PartyMemberModel())->add($partyId, $userId);

        $party             = $partyModel->findById($partyId);
        $party['is_member']  = true;
        $party['can_manage'] = true;
        return $party;
    }

    // ── Détail d'une partie ───────────────────────────────────────────────────

    #[Endpoint('get', '/party/{id}', 'Get party details')]
    public function getParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $party = $this->getPartyOrFail($partyId);

        $memberModel         = new PartyMemberModel();
        $isMember            = $memberModel->isMember($partyId, $userId);
        $canManage           = (new PartyModel())->canManage($partyId, $userId);

        // Partie privée : accès réservé aux membres et gestionnaires
        if (!$party['is_public'] && !$isMember && !$canManage) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $party['members']    = $memberModel->getMembers($partyId);
        $party['is_member']  = $isMember;
        $party['can_manage'] = $canManage;

        if (!$isMember && !$canManage) {
            unset($party['invite_code']);
        }

        return $party;
    }

    // ── Modifier une partie ───────────────────────────────────────────────────

    #[Endpoint('put', '/party/{id}', 'Update party')]
    public function updateParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $party = $this->getPartyOrFail($partyId);
        $this->requireCanManage($partyId, $userId);

        (new PartyModel())->update($partyId, $this->body());
        return (new PartyModel())->findById($partyId);
    }

    // ── Supprimer une partie ──────────────────────────────────────────────────

    #[Endpoint('delete', '/party/{id}', 'Delete party')]
    public function deleteParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $this->getPartyOrFail($partyId);
        $this->requireCanManage($partyId, $userId);

        (new PartyModel())->delete($partyId);
        return ['deleted' => true];
    }

    // ── Lancer / arrêter ──────────────────────────────────────────────────────

    #[Endpoint('post', '/party/{id}/start', 'Start party session')]
    public function startParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $this->getPartyOrFail($partyId);
        $this->requireCanManage($partyId, $userId);

        (new PartyModel())->setActive($partyId, true);
        return ['is_active' => true];
    }

    #[Endpoint('post', '/party/{id}/stop', 'Stop party session')]
    public function stopParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $this->getPartyOrFail($partyId);
        $this->requireCanManage($partyId, $userId);

        (new PartyModel())->setActive($partyId, false);
        return ['is_active' => false];
    }

    // ── Rejoindre ─────────────────────────────────────────────────────────────

    #[Endpoint('post', '/party/{id}/join', 'Join a party')]
    public function joinParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $party = $this->getPartyOrFail($partyId);

        $memberModel = new PartyMemberModel();
        if ($memberModel->isMember($partyId, $userId)) {
            return ['message' => 'Already a member.'];
        }

        // Vérifier le code d'invitation pour les parties privées
        if (!$party['is_public']) {
            $body       = $this->body();
            $code       = trim(strtoupper((string) ($body['invite_code'] ?? '')));
            if ($code === '' || $code !== $party['invite_code']) {
                jsonResponse(false, [], ErrorCodes::SERVER_INVITE_INVALID, 403);
            }
        }

        // Vérifier la capacité
        if ($memberModel->countMembers($partyId) >= (int) $party['max_players']) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $body        = $this->body();
        $characterId = isset($body['character_id']) ? (int) $body['character_id'] : null;

        if ($characterId !== null) {
            $char = (new CharacterModel())->findById($characterId);
            if (!$char || (int) $char['user_id'] !== $userId) {
                jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
            }
        }

        $memberModel->add($partyId, $userId, $characterId);
        return ['joined' => true, 'party_id' => $partyId];
    }

    // ── Quitter ───────────────────────────────────────────────────────────────

    #[Endpoint('post', '/party/{id}/leave', 'Leave a party')]
    public function leaveParty(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $this->getPartyOrFail($partyId);
        (new PartyMemberModel())->remove($partyId, $userId);
        return ['left' => true];
    }

    // ── Changer de personnage ─────────────────────────────────────────────────

    #[Endpoint('put', '/party/{id}/character', 'Set character for party')]
    public function setCharacter(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $this->getPartyOrFail($partyId);

        $body        = $this->body();
        $characterId = isset($body['character_id']) ? (int) $body['character_id'] : null;

        if ($characterId !== null) {
            $char = (new CharacterModel())->findById($characterId);
            if (!$char || (int) $char['user_id'] !== $userId) {
                jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
            }
        }

        (new PartyMemberModel())->setCharacter($partyId, $userId, $characterId);
        return ['updated' => true];
    }

    // ── Membres ───────────────────────────────────────────────────────────────

    #[Endpoint('get', '/party/{id}/members', 'Get party members')]
    public function getMembers(string $id): array {
        $userId  = $this->requireAuth();
        $partyId = (int) $id;

        $party = $this->getPartyOrFail($partyId);

        if (!$party['is_public']) {
            $memberModel = new PartyMemberModel();
            if (!$memberModel->isMember($partyId, $userId) && !(new PartyModel())->canManage($partyId, $userId)) {
                jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
            }
        }

        return (new PartyMemberModel())->getMembers($partyId);
    }

}
