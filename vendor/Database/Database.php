<?php

namespace SariwonAPI\Database;

use \PDO;
use \PDOException;

use SariwonAPI\Config\Configuration;

class Database {

	private $host;
	private $name;
	private $user;
	private $pass;
	private $connexion;
	
    private $lastStatement;

	private static $instance = null;
									
	function __construct($host = null, $name = null, $user = null, $pass = null){
		Configuration::getInstance();
		$config = Configuration::getInstance();
		if ($host != null) {
			$this->host = $host;
			$this->name = $name;
			$this->user = $user;
			$this->pass = $pass;
		} elseif ($config !== false) {
			$this->host = $config->get("database", "host");
			$this->name = $config->get("database", "name");
			$this->user = $config->get("database", "user");
			$this->pass = $config->get("database", "pass");
		} else {
			throw new \Exception("Database configuration not found.");
		}
		try{
			$this->connexion = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->name,
				$this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND =>'SET NAMES UTF8', 
				PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		}catch (PDOException $e){
			if ($config->get('core', 'debug/show_err') == true) {
				echo 'Erreur : DB connection failed ! <br><br>'.$e;
			} else {
				echo 'Erreur : DB connection failed !';
			}
			die();
		}
	}
	
	// Singleton : récupération de l'instance unique
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Database();
		}
		return self::$instance;
	}

	public function getHost() : ?string {
		return $this->host;
	}

	public function getName() : ?string {
		return $this->name;
	}

	public function getUser() : ?string {
		return $this->user;
	}

	public static function getInformations() : array {
		return [
			"host" => self::getInstance()->getHost(),
			"name" => self::getInstance()->getName(),
			"user" => self::getInstance()->getUser()
		];
	}
	
	public function query($sql, $data = array()) {
		$this->lastStatement = $this->connexion->prepare($sql);
		return $this->lastStatement->execute($data);
	}
	
	public function executeNonQuery($sql, $data = array()) {
		$this->lastStatement = $this->connexion->prepare($sql);
		$this->lastStatement->execute($data);
	}

	public function rowCount() : int {
		return $this->lastStatement ? $this->lastStatement->rowCount() : 0;
	}

	public function fetchAll() {
		return $this->lastStatement->fetchAll();
	}

	public function fetchAllColumns() {
		return $this->lastStatement->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	public function getId() {
		return $this->connexion->lastInsertId();
	}

	public function getLastError() : ?string {
        if ($this->lastStatement) {
            $error = $this->lastStatement->errorInfo();
            return $error[2] ?? null;
        }
        return null;
    }

}

?>