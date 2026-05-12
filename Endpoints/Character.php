<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\CharacterModel;

class Character extends AbstractEndpoint {

    #[Endpoint('get', '/character', 'Get My Characters', 'restricted')]
    public function getMyCharacters(): array {
        $userId = $this->requireAuth();
        $model  = new CharacterModel();
        return $model->findByUser($userId);
    }

    #[Endpoint('get', '/character/{id}', 'Get Character Infos')]
    public function getCharacter(string $id): array {
        $model     = new CharacterModel();
        $character = $model->findById((int) $id);

        if (!$character) {
            jsonResponse(false, [], \ErrorCodes::CHAR_NOT_FOUND, 404);
        }

        return $character;
    }

    #[Endpoint('post', '/character', 'Create Character', 'restricted')]
    public function createCharacter(): array {
        $userId = $this->requireAuth();
        $body   = $this->body();

        if (empty($body['name']) || empty($body['server_id']) || empty($body['species_id'])) {
            jsonResponse(false, [], \ErrorCodes::MISSING_PARAMETERS, 400);
        }

        $model  = new CharacterModel();
        $charId = $model->create($userId, $body);

        $character = $model->findById($charId);

        if (!$character) {
            jsonResponse(false, [], \ErrorCodes::INTERNAL_SERVER_ERROR, 500);
        }

        return $character;
    }

    #[Endpoint('put', '/character/{id}', 'Update Character Infos', 'restricted')]
    public function updateCharacter(string $id): array {
        $userId = $this->requireAuth();
        $charId = (int) $id;
        $model  = new CharacterModel();

        if (!$model->belongsToUser($charId, $userId)) {
            jsonResponse(false, [], \ErrorCodes::CHAR_FORBIDDEN, 403);
        }

        $body = $this->body();
        $model->update($charId, $body);

        $character = $model->findById($charId);

        if (!$character) {
            jsonResponse(false, [], \ErrorCodes::CHAR_NOT_FOUND, 404);
        }

        return $character;
    }

    #[Endpoint('delete', '/character/{id}', 'Delete Character', 'restricted')]
    public function deleteCharacter(string $id): array {
        $userId = $this->requireAuth();
        $charId = (int) $id;
        $model  = new CharacterModel();

        if (!$model->belongsToUser($charId, $userId)) {
            jsonResponse(false, [], \ErrorCodes::CHAR_FORBIDDEN, 403);
        }

        $model->delete($charId);

        return ['message' => 'Character deleted.'];
    }

}
