<?php

namespace SariwonAPI\Models;

class UserModel extends Model {

    public function __construct() {
        parent::__construct('user');
    }

    public function findById(int $id): ?array {
        $this->query(
            'SELECT `id`, `username`, `uniquename`, `biography`, `email`,
                    `recovery_email`, `phone_e164`, `country_code`, `tag_id`
             FROM `user` WHERE `id` = :id LIMIT 1',
            ['id' => $id]
        );
        return $this->fetchOne();
    }

    public function findByEmail(string $email): ?array {
        $this->query(
            'SELECT `id`, `username`, `uniquename`, `email`, `password`
             FROM `user` WHERE `email` = :email LIMIT 1',
            ['email' => $email]
        );
        return $this->fetchOne();
    }

    public function emailExists(string $email): bool {
        $this->query(
            'SELECT `id` FROM `user` WHERE `email` = :email LIMIT 1',
            ['email' => $email]
        );
        return $this->rowCount() > 0;
    }

    public function uniquenameExists(string $uniquename): bool {
        $this->query(
            'SELECT `id` FROM `user` WHERE `uniquename` = :u LIMIT 1',
            ['u' => $uniquename]
        );
        return $this->rowCount() > 0;
    }

    public function create(string $username, string $uniquename, string $email, string $passwordHash): int {
        $this->query(
            'INSERT INTO `user` (`username`, `uniquename`, `email`, `password`)
             VALUES (:username, :uniquename, :email, :password)',
            [
                'username'   => $username,
                'uniquename' => $uniquename,
                'email'      => $email,
                'password'   => $passwordHash,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['username', 'biography', 'recovery_email', 'phone_e164', 'country_code', 'phone_local'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        if (empty($fields)) return false;

        $sets = implode(', ', array_map(fn(string $k): string => "`$k` = :$k", array_keys($fields)));
        $fields['id'] = $id;

        return $this->query("UPDATE `user` SET $sets WHERE `id` = :id", $fields);
    }

    public function delete(int $id): bool {
        return $this->query('DELETE FROM `user` WHERE `id` = :id', ['id' => $id]);
    }

    public function generateUniquename(string $username): string {
        $base      = strtolower(preg_replace('/[^a-z0-9_]/i', '', str_replace(' ', '_', $username)) ?? '');
        $base      = $base !== '' ? $base : 'user';
        $candidate = $base;

        while ($this->uniquenameExists($candidate)) {
            $candidate = $base . '_' . rand(1000, 9999);
        }

        return $candidate;
    }

}
