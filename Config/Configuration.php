<?php

namespace SariwonAPI\Config;

class Configuration {

    private static ?Configuration $instance = null;

    private array $config;

    private function __construct() {
        $this->config['core'] = parse_ini_file('core.ini', true);
        $this->config['database'] = parse_ini_file('database.ini', false);
    }

    public static function getInstance(): Configuration {
        if (self::$instance === null) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }

    public function get($file, $key, $default = null) {
        $keyList = explode('/', $key);
        switch ($file) {
            case 'database':
                return !empty($this->config['database'][$key]) ? $this->config['database'][$key] : $default;
                break;
            case 'core':
                $tempList = $this->config['core'];
                foreach ($keyList as $k) {
                    if (isset($tempList[$k])) {
                        $tempList = $tempList[$k];
                    } else {
                        return $default;
                    }
                }
                return $tempList;
                break;
            default:
                return $default;
                break;
        }
    }

}