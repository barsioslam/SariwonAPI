<?php

namespace SariwonAPI\Models;

use SariwonAPI\Database\Database;

abstract class Model {

    protected string $table;

    public function __construct(string $table) {
        $this->table = $table;
    }

    protected function db(): Database {
        return Database::getInstance();
    }

    protected function query(string $sql, array $params = []): bool {
        return $this->db()->query($sql, $params);
    }

    protected function fetchAll(): array {
        return $this->db()->fetchAll() ?: [];
    }

    protected function fetchOne(): ?array {
        $results = $this->db()->fetchAll();
        return $results ? $results[0] : null;
    }

    protected function lastId(): int {
        return (int) $this->db()->getId();
    }

    protected function rowCount(): int {
        return $this->db()->rowCount();
    }

    public function getLastError(): ?string {
        return $this->db()->getLastError();
    }

}
