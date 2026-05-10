<?php
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");

    require_once __DIR__ . '/../core/http/Router.php';
    require_once __DIR__ . '/../utils/utils.php';

    $router = new Router();

    $routes = require __DIR__ . '/../routes/web.php';
    $routes($router);

    $route = isset($_GET["route"]) ? '/' . $_GET["route"] : str_replace('/EnergySaveProject/src', '', $_SERVER['REQUEST_URI']);

    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        $route
    );