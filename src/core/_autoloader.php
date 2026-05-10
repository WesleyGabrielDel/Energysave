<?php 

spl_autoload_register(function ($class) {

    $baseDir = __DIR__ . "/";
    $folders = ["services", "validators", "repositories", "http"];

    foreach ($folders as $folder) {
        $path = $baseDir . $folder;

        if (!is_dir($path)) continue;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                if ($fileInfo->getFilename() === $class . ".php") {
                    require_once $fileInfo->getPathname();
                    return;
                }
            }
        }
    }
});