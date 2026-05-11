<?php 

namespace SariwonAPI\Models;

use SariwonAPI\Database\Database;

abstract class Model {

    private string $tableName;

    public function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function get() {
        $sql = "SELECT * FROM {$this->tableName}";
        return $this->req($sql);
    }

    public function req($sql, $params = []) {
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}