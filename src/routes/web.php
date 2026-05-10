<?php 

require "../../bootstrap.php";

return function($router) {

    require "endpoints/_autoloader.php";

    $router->get('/auth', function () {
        return view("auth");
    });

    $router->get('/landing', function () {
        return view("index");
    });
        
    $router->get('/myaccount', function () {
        return view("account-config");
    });

    $router->get('/device-settings', function () {
        return view("configurar-dispositivo");
    });

    $router->get('/home', function () {
        return view("home-page");
    });

};