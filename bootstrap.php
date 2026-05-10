<?php

//----------------------------------------------------
// CARREGANDO OS DIRETÓRIOS DO PROJETO
//--------------------------------------------------

define('BASE_DIR', __DIR__);
define('DONTENV_PATH', BASE_DIR . "/.env");
define('BOOTSTRAP_PATH', BASE_DIR . "/bootstrap.php");

// Public 
define('PUBLIC_PATH', BASE_DIR . "/public/");
define('ASSETS_PATH', PUBLIC_PATH);
define('IMAGES_PATH', ASSETS_PATH . "images/");
define('AVATARS_PATH', IMAGES_PATH . "avatars/");
define('JS_PATH', ASSETS_PATH . "js/");
define('CSS_PATH', ASSETS_PATH . "css/");

// Controller 
define('CONTROLLER_PATH', BASE_DIR . "/src/");
define('ROUTER_PATH', CONTROLLER_PATH . "index.php");
define('PYTHON_PATH', CONTROLLER_PATH . "app.py");
define('CONTROLLER_CORE_PATH', CONTROLLER_PATH . "core/");
define('CONTROLLER_AUTOLOAD_PATH', CONTROLLER_CORE_PATH . "_autoloader.php");

// Utils 
define('UTILS_PATH', CONTROLLER_PATH . "/utils/");
define('UTILS_CORE_PATH', UTILS_PATH . "core/");
define('UTILS_AUTOLOAD_PATH', UTILS_CORE_PATH . "_autoloader.php");

// Api
define('API_PATH', BASE_DIR . "/api/");
define('HELPERS_PATH', API_PATH . "helpers/");
define('SERVICES_PATH', API_PATH . "services/");

//----------------------------------------------------
// CARREGANDO AS CLASSES
//--------------------------------------------------

require_once UTILS_PATH . "utils.php";
require_once UTILS_AUTOLOAD_PATH;
require_once CONTROLLER_AUTOLOAD_PATH;

function view($file, $data = [])
{
    header("Content-Type: text/html; charset=UTF-8");
    extract($data); // Converte o array em variáveis

    ob_start();
    require BASE_DIR . "/views/$file.php"; // Carrega a view
    echo ob_get_clean(); // Retorna o conteúdo da view e carrega a página
}

//----------------------------------------------------
// CARREGANDO AS VARIÁVEIS DE AMBIENTE
//--------------------------------------------------

$fields = Env::get();

define("API_SECRET", $fields["API_SECRET"] ?? null);
define('BASE_URL', '/EnergySaveProject/');
define("API_KEY", $fields["API_KEY"] ?? null);

define("DEBUG", $fields["DEBUG"] ?? null);
define("ORIGIN", $fields["ORIGIN"] ?? null);

define("DBUSER", $fields["DBUSER"] ?? null);
define("DBPASSWORD", $fields["DBPASSWORD"] ?? null);
define("DBNAME", $fields["DBNAME"] ?? null);
define("DBHOST", $fields["DBHOST"] ?? null);