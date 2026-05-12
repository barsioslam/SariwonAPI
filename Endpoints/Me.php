<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\UserModel;
use SariwonAPI\Config\ErrorCodes;

class Me extends AbstractEndpoint {

    #[Endpoint('get', '/me', 'Get Self User Infos')]
    public function getMe(): array {
        $userId = $this->requireAuth();
        $model  = new UserModel();
        $user   = $model->findById($userId);

        if (!$user) {
            jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        }

        return $user;
    }

    #[Endpoint('put', '/me', 'Update Self User Infos')]
    public function updateMe(): array {
        $userId = $this->requireAuth();
        $body   = $this->body();
        $model  = new UserModel();

        $model->update($userId, $body);

        $user = $model->findById($userId);

        if (!$user) {
            jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        }

        return $user;
    }

    #[Endpoint('delete', '/me', 'Delete Self User')]
    public function deleteMe(): array {
        $userId = $this->requireAuth();
        $model  = new UserModel();

        $model->delete($userId);
        session_destroy();

        return ['message' => 'Account deleted.'];
    }

}
