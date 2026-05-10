<?php 

$router->get('/getUserSessions', function(){
    $data = UserSessions::getSessions();
    echo $data;
});

$router->post('/revokeSession', function(){
    $data = getJson();
    UserSessions::revokeSession($data);
});

$router->get('/revokeAll', function(){
    UserSessions::revokeAll();
});

$router->post('/deleteAccount', function(){
    UserService::deleteAccount();
});

$router->get('/isLogged', function(){
    echo json_encode([
        "success" => true, 
        "message" => "...", 
        "data" => UserSessions::isLoggedIn()
    ]);
});