<?php

$namespaces = [

    "SariwonAPI\\Config\\"        => BASE_DIR . "Config/",    
    "SariwonAPI\\Database\\"      => VENDOR_DIR . "/Database/",
    "SariwonAPI\\Models\\"        => MODELS_DIR,
    "SariwonAPI\\Endpoints\\"     => ENDPOINTS_DIR,

];

spl_autoload_register(function ($class) use ($namespaces) {

    foreach ($namespaces as $prefix => $baseDir) {
        $len = strlen($prefix);

        // Vérifie si la classe commence par le namespace
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Récupère la partie après le namespace
        $relativeClass = substr($class, $len);

        // Transforme les "\" en "/" pour les sous-namespaces
        $file = rtrim($baseDir, '/\\') . '/' . str_replace("\\", "/", $relativeClass) . ".php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

});

function listLoadableClasses(array $namespaces): array {
    $classes = [];

    foreach ($namespaces as $prefix => $baseDir) {
        if (!is_dir($baseDir)) {
            continue;
        }

        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));

        foreach ($rii as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            // Récupère le chemin relatif au dossier de base
            $relativePath = substr($file->getPathname(), strlen($baseDir));
            $relativeClass = str_replace('/', '\\', substr($relativePath, 0, -4)); // -4 pour enlever ".php"

            $classes[] = $prefix . $relativeClass;
        }
    }

    return $classes;
}

?>