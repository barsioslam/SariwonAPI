<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\ServerModel;
use SariwonAPI\Models\ServerFavoriteModel;
use SariwonAPI\Models\CharacterModel;
use SariwonAPI\Models\SpeciesModel;
use SariwonAPI\Models\CharacterClassModel;
use SariwonAPI\Models\CharacterJobModel;
use SariwonAPI\Models\AlignmentModel;
use SariwonAPI\Config\ErrorCodes;

class Server extends AbstractEndpoint {

    // ── Serveurs favoris de l'utilisateur ────────────────────────────────────

    #[Endpoint('get', '/servers', 'Get user servers (owned, favorited, party member)')]
    public function getMyServers(): array {
        $userId = $this->requireAuth();
        return (new ServerModel())->findForUser($userId);
    }

    // ── Recherche / browse public ─────────────────────────────────────────────

    #[Endpoint('get', '/servers/browse', 'Browse public servers')]
    public function browseServers(): array {
        $this->requireAuth();
        $q = trim((string) ($_GET['q'] ?? ''));
        $model = new ServerModel();
        return $q !== '' ? $model->search($q) : $model->findPublic();
    }

    // ── Détail d'un serveur (accessible à tous) ───────────────────────────────

    #[Endpoint('get', '/server/{id}', 'Get server infos')]
    public function getServer(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $model  = new ServerModel();
        $server = $model->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        $server['is_owner']     = $model->isOwner($serverId, $userId);
        $server['is_favorited'] = (new ServerFavoriteModel())->isFavorited($userId, $serverId);

        return $server;
    }

    // ── Créer un serveur ──────────────────────────────────────────────────────

    #[Endpoint('post', '/servers', 'Create server')]
    public function createServer(): array {
        $userId = $this->requireAuth();
        $body   = $this->body();

        $name          = trim((string) ($body['name']          ?? ''));
        $description   = trim((string) ($body['description']   ?? ''));
        $ageRestricted = (bool)        ($body['age_restricted'] ?? false);
        $genre         = trim((string) ($body['genre']         ?? '')) ?: null;

        if ($name === '' || strlen($name) < 3 || strlen($name) > 60) {
            jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);
        }

        $serverModel = new ServerModel();
        $serverId    = $serverModel->create($userId, $name, $description, $ageRestricted, $genre);

        if (!$serverId) {
            jsonResponse(false, ['message' => $serverModel->getLastError() ?? 'Insert failed'], ErrorCodes::INTERNAL_SERVER_ERROR, 500);
        }

        // Créateur met en favoris automatiquement
        (new ServerFavoriteModel())->add($userId, $serverId);

        return [
            'id'             => $serverId,
            'name'           => $name,
            'description'    => $description,
            'age_restricted' => $ageRestricted,
            'genre'          => $genre,
            'is_owner'       => true,
            'is_favorited'   => true,
            'party_count'    => 0,
        ];
    }

    // ── Modifier un serveur (GM uniquement) ───────────────────────────────────

    #[Endpoint('put', '/server/{id}', 'Update server infos')]
    public function updateServer(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $model  = new ServerModel();
        $server = $model->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        if (!$model->isOwner($serverId, $userId)) jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);

        $body    = $this->body();
        $allowed = ['name', 'description', 'age_restricted', 'genre'];
        $data    = array_intersect_key($body, array_flip($allowed));

        $model->update($serverId, $data);
        return $model->findById($serverId);
    }

    // ── Supprimer un serveur ──────────────────────────────────────────────────

    #[Endpoint('delete', '/server/{id}', 'Delete server')]
    public function deleteServer(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $model  = new ServerModel();
        $server = $model->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);
        if (!$model->isOwner($serverId, $userId)) jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);

        $model->delete($serverId);
        return ['deleted' => true];
    }

    // ── Favoris : ajouter / retirer ───────────────────────────────────────────

    #[Endpoint('post', '/server/{id}/favorite', 'Add server to favorites')]
    public function addFavorite(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        (new ServerFavoriteModel())->add($userId, $serverId);
        return ['favorited' => true];
    }

    #[Endpoint('delete', '/server/{id}/favorite', 'Remove server from favorites')]
    public function removeFavorite(string $id): array {
        $userId   = $this->requireAuth();
        $serverId = (int) $id;

        (new ServerFavoriteModel())->remove($userId, $serverId);
        return ['favorited' => false];
    }

    // ── Lookups live du serveur (brouillon GM) ────────────────────────────────

    #[Endpoint('get', '/server/{id}/lookups', 'Get server live lookups')]
    public function getServerLookups(string $id): array {
        $this->requireAuth();
        $serverId = (int) $id;

        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        return [
            'species'    => (new SpeciesModel())->findForServer($serverId),
            'classes'    => (new CharacterClassModel())->findForServer($serverId),
            'jobs'       => (new CharacterJobModel())->findForServer($serverId),
            'alignments' => (new AlignmentModel())->findForServer($serverId),
        ];
    }

    // ── Personnages du serveur ────────────────────────────────────────────────

    #[Endpoint('get', '/server/{id}/characters', 'Get server characters')]
    public function getServerCharacters(string $id): array {
        $this->requireAuth();
        $serverId = (int) $id;

        $server = (new ServerModel())->findById($serverId);
        if (!$server) jsonResponse(false, [], ErrorCodes::SERVER_NOT_FOUND, 404);

        return (new CharacterModel())->findByServer($serverId);
    }

}
