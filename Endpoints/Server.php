<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\ServerModel;
use SariwonAPI\Models\ServerMemberModel;
use SariwonAPI\Config\ErrorCodes;

class Server extends AbstractEndpoint {

    #[Endpoint('get', '/servers', 'Get user servers')]
    public function getMyServers(): array {
        $userId = $this->requireAuth();
        $model  = new ServerModel();
        return $model->findByUser($userId);
    }

    #[Endpoint('get', '/servers/public', 'Get public servers')]
    public function getPublicServers(): array {
        $model = new ServerModel();
        return $model->findPublic();
    }

    #[Endpoint('get', '/server/{id}', 'Get server infos')]
    public function getServer(): array {
        $userId   = $this->requireAuth();
        $serverId = (int) ($_GET['id'] ?? 0);

        $model  = new ServerModel();
        $server = $model->findById($serverId);

        if (!$server) {
            jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        }

        $members = new ServerMemberModel();
        if ($server['visibility'] === 'private' && !$members->isMember($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $server['member_count'] = $members->countMembers($serverId);
        return $server;
    }

    #[Endpoint('post', '/servers', 'Create server')]
    public function createServer(): array {
        $userId = $this->requireAuth();
        $body   = $this->body();

        $name          = trim((string) ($body['name']          ?? ''));
        $description   = trim((string) ($body['description']   ?? ''));
        $visibility    = trim((string) ($body['visibility']    ?? 'public'));
        $maxPlayers    = (int)          ($body['maxPlayers']   ?? 8);
        $inviteCode    = trim((string) ($body['inviteCode']    ?? '')) ?: null;
        $ageRestricted = (bool)        ($body['ageRestricted'] ?? false);
        $genre         = trim((string) ($body['genre']         ?? '')) ?: null;

        // Validation
        if ($name === '') {
            jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);
        }
        if (strlen($name) < 3 || strlen($name) > 60) {
            jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
        }
        if (!in_array($visibility, ['public', 'private'], true)) {
            jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
        }
        if ($maxPlayers < 2 || $maxPlayers > 30) {
            jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
        }

        $serverModel = new ServerModel();

        // Vérifier unicité du code d'invitation personnalisé
        if ($inviteCode !== null) {
            if (strlen($inviteCode) > 16 || !preg_match('/^[A-Z0-9\-]+$/', $inviteCode)) {
                jsonResponse(false, [], ErrorCodes::INVALID_PARAMETERS, 400);
            }
            if ($serverModel->inviteCodeExists($inviteCode)) {
                jsonResponse(false, [], ErrorCodes::SERVER_INVITE_TAKEN, 409);
            }
        } else {
            // Générer un code unique automatiquement
            do {
                $inviteCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            } while ($serverModel->inviteCodeExists($inviteCode));
        }

        $serverId = $serverModel->create(
            $userId,
            $name,
            $description,
            $visibility,
            $maxPlayers,
            $inviteCode,
            $ageRestricted,
            $genre
        );

        if (!$serverId) {
            jsonResponse(false, [
                'message' => $serverModel->getLastError() ?? 'Insert failed',
            ], ErrorCodes::INTERNAL_SERVER_ERROR, 500);
        }

        // Ajouter le créateur comme membre GM
        $memberModel = new ServerMemberModel();
        $memberModel->add($serverId, $userId, 'gm');

        return [
            'id'            => $serverId,
            'name'          => $name,
            'description'   => $description,
            'visibility'    => $visibility,
            'max_players'   => $maxPlayers,
            'invite_code'   => $inviteCode,
            'age_restricted'=> $ageRestricted,
            'genre'         => $genre,
            'role'          => 'gm',
            'member_count'  => 1,
        ];
    }

    #[Endpoint('put', '/server/{id}', 'Update server infos', 'restricted')]
    public function updateServer(): array {
        $userId   = $this->requireAuth();
        $serverId = (int) ($_GET['id'] ?? 0);

        $model  = new ServerModel();
        $server = $model->findById($serverId);

        if (!$server) {
            jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        }
        if (!$model->isOwner($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $body    = $this->body();
        $allowed = ['name', 'description', 'visibility', 'max_players', 'invite_code', 'age_restricted', 'genre'];
        $data    = array_intersect_key($body, array_flip($allowed));

        $model->update($serverId, $data);
        return $model->findById($serverId);
    }

    #[Endpoint('delete', '/server/{id}', 'Delete server', 'restricted')]
    public function deleteServer(): array {
        $userId   = $this->requireAuth();
        $serverId = (int) ($_GET['id'] ?? 0);

        $model  = new ServerModel();
        $server = $model->findById($serverId);

        if (!$server) {
            jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        }
        if (!$model->isOwner($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $model->delete($serverId);
        return ['message' => 'Server deleted.'];
    }

    #[Endpoint('get', '/server/{id}/members', 'Get server members')]
    public function getServerMembers(): array {
        $userId   = $this->requireAuth();
        $serverId = (int) ($_GET['id'] ?? 0);

        $serverModel = new ServerModel();
        $server      = $serverModel->findById($serverId);

        if (!$server) {
            jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        }

        $memberModel = new ServerMemberModel();
        if (!$memberModel->isMember($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        return $memberModel->countMembers($serverId) ? [] : [];
    }

    #[Endpoint('post', '/server/join', 'Join server by invite code')]
    public function joinServer(): array {
        $userId = $this->requireAuth();
        $body   = $this->body();

        $inviteCode = trim(strtoupper((string) ($body['invite_code'] ?? '')));
        if ($inviteCode === '') {
            jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);
        }

        $serverModel = new ServerModel();
        $server      = $serverModel->findByInviteCode($inviteCode);

        if (!$server) {
            jsonResponse(false, [], ErrorCodes::SERVER_INVITE_INVALID, 404);
        }

        $memberModel = new ServerMemberModel();
        if ($memberModel->isMember($server['id'], $userId)) {
            return ['message' => 'Already a member.', 'server_id' => $server['id']];
        }

        $count = $memberModel->countMembers($server['id']);
        if ($count >= $server['max_players']) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }

        $memberModel->add($server['id'], $userId, 'player');

        return [
            'server_id'  => $server['id'],
            'name'       => $server['name'],
            'role'       => 'player',
        ];
    }

}