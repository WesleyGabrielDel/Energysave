<?php 

// Pega todos os arquivos dentro de endpoints
$files = array_values(array_filter(scandir(__DIR__), function ($file) {
    return is_file(__DIR__ . "/" . $file);
}));

foreach ($files as $file) {
    // Se o arquivo não for o autoloader, ele vai fazer um require
    if($file !== __DIR__ . "/" . "_autoloader.php"){
        require_once $file;
    }
}