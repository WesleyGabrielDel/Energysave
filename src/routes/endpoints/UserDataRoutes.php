<?php 

$router->get('/getUserData', function(){
    UserData::getInfo();
});

$router->post('/updateUserData', function(){
    $data = getJson();
    UserData::updateData($data);
});