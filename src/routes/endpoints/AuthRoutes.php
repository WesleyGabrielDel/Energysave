<?php 

$router->post('/login', function(){
    $data = getJson();
    $status = AuthService::login($data);
    echo json_encode($status);
});

$router->post('/signup', function(){
    $data = getJson();
    $status = AuthService::signUp($data);
    echo json_encode($status);
});

$router->post('/google', function(){
    $data = getJson();
    $status = AuthService::googleAuth($data);

    if (($data["cadastro"] ?? false) && ($status["success"] ?? false)) {
        $user_id = $status["user_id"];

        $token_row = SessionService::createSessionCookie("on", false, $user_id, [], true);
        $token_id = $token_row["token_id"];
        
        UserSessions::saveSession($user_id, $token_id);
    }

    echo json_encode($status);
});

$router->get('/logout', function(){
    $status = AuthService::logout();
    echo json_encode($status);
});

$router->post('/resend-code', function(){
    $data = getJson();
    $status = AuthService::reSendCode($data);

    echo json_encode($status);
});

$router->get('/passkey-challenge', function(){
    $challenge = random_bytes(32);
    echo json_encode([
        'challenge' => base64_encode($challenge)
    ]);
});