<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\UserModel;

class Auth extends AbstractEndpoint {

    #[Endpoint('post', '/auth/login', 'Login')]
    public function login(): array {
        $body     = $this->body();
        $email    = trim((string) ($body['email']    ?? ''));
        $password =       (string) ($body['password'] ?? '');

        if ($email === '' || $password === '') {
            jsonResponse(false, [], \ErrorCodes::MISSING_PARAMETERS, 400);
        }

        $model = new UserModel();
        $user  = $model->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(false, [], \ErrorCodes::AUTH_INVALID_CREDENTIALS, 401);
        }

        $_SESSION['user_id'] = $user['id'];

        return [
            'id'         => $user['id'],
            'username'   => $user['username'],
            'uniquename' => $user['uniquename'],
            'email'      => $user['email'],
        ];
    }

    #[Endpoint('post', '/auth/register', 'Register')]
    public function register(): array {
        $body     = $this->body();
        $username = trim((string) ($body['username'] ?? ''));
        $email    = trim((string) ($body['email']    ?? ''));
        $password =       (string) ($body['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            jsonResponse(false, [], \ErrorCodes::MISSING_PARAMETERS, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, [], \ErrorCodes::INVALID_PARAMETERS, 400);
        }

        if (strlen($password) < 8) {
            jsonResponse(false, [], \ErrorCodes::INVALID_PARAMETERS, 400);
        }

        $model = new UserModel();

        if ($model->emailExists($email)) {
            jsonResponse(false, [], \ErrorCodes::AUTH_EMAIL_TAKEN, 409);
        }

        $uniquename   = $model->generateUniquename($username);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userId       = $model->create($username, $uniquename, $email, $passwordHash);

        $_SESSION['user_id'] = $userId;

        return [
            'id'         => $userId,
            'username'   => $username,
            'uniquename' => $uniquename,
            'email'      => $email,
        ];
    }

    #[Endpoint('post', '/auth/logout', 'Logout')]
    public function logout(): array {
        session_destroy();
        return ['message' => 'Logged out successfully.'];
    }

}
