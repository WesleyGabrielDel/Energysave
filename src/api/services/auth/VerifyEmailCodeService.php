<?php

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://localhost:3000");

require "../../../../bootstrap.php";

$data = getJson();

$userCode = $data["userCode"] ?? null;
$email = $data["email"] ?? null;
$password = $data["password"] ?? null;
$name = $data["name"] ?? null;
$emailType = $data["emailType"] ?? $data["type"] ?? "cadastro";
$remember = filter_var($data["remember"] ?? false, FILTER_VALIDATE_BOOLEAN);

$mysqli = Database::connect();

$row = Database::query($mysqli, "SELECT time_exp FROM email_codes WHERE email = ?", true, "s", [$email]);
if (!$row) {
    echo json_encode(["message" => "Nenhum código encontrado para este email!", "typeMessage" => "error"]);
    exit;
}

if (intval($row["time_exp"]) < time()) {
    Database::query($mysqli, "DELETE FROM email_codes WHERE email = ?", false, "s", [$email]);
    echo json_encode([
        "message" => "Este código está expirado! Clique no botão para que um novo código seja enviado!",
        "typeMessage" => "error"
    ]);
    exit;
}

$row = Database::query($mysqli, "SELECT codigo FROM email_codes WHERE email = ?", true, "s", [$email]);
if (!$row) {
    echo json_encode(["success" => false, "message" => "Nenhum código encontrado para este email!", "typeMessage" => "error"]);
    exit;
}

if (strval($row["codigo"]) !== strval($userCode)) {
    echo json_encode(["success" => false, "message" => "Código inválido inserido!", "typeMessage" => "error"]);
    exit;
} 
    
else {
    Database::query($mysqli, "DELETE FROM email_codes WHERE email = ? AND codigo = ?", false, "ss", [$email, $userCode]);
}

$result = Database::query($mysqli, "SELECT id FROM user WHERE email = ?", true, "s", [$email]);
if ($result === null) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $defaultAvatar = BASE_URL . "public/images/avatars/logo-conta.png";

    $stmt = $mysqli->prepare("INSERT INTO user (nome, email, senha, profile_picture) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Erro interno", "error" => $mysqli->error]);
        exit;
    }

    $stmt->bind_param("ssss", $name, $email, $passwordHash, $defaultAvatar);
    if ($stmt->execute()) {

        $userId = $stmt->insert_id;
        $stmt->close();

        Database::query($mysqli, "INSERT INTO users_config (user_id) VALUES (?)", false, "i", [$userId]);
            
        $token_row = SessionService::createSessionCookie("off", false, $userId, [], true);
        $token_id = $token_row["token_id"];
        UserSessions::saveSession($userId, $token_id);
            
        echo json_encode([
            "success" => true,
            "message" => "Usuário cadastrado com sucesso!",
            "typeMessage" => "success",
            "changeLocation" => true
        ]);

    } 
        
    else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao cadastrar o usuário",
            "typeMessage" => "error"
        ]);
    }
    exit;
}

if ($emailType === "2fa") {
    $userId = UserRepository::findIdByEmail($email);
    if (!$userId || empty($userId["id"])) {
        echo json_encode(["success" => false, "message" => "Usuário não encontrado para o 2FA.", "typeMessage" => "error"]);
        exit;
    }

    $token_row = SessionService::createSessionCookie($remember, false, $userId["id"], [], true);
    UserSessions::saveSession($userId["id"], $token_row["token_id"]);

    echo json_encode([
        "success" => true,
        "message" => "Login efetuado com sucesso!",
        "typeMessage" => "success",
        "changeLocation" => true
    ]);
    exit;
}

echo json_encode([
    "message" => "Usuário já cadastrado!",
    "typeMessage" => "error"
]);
exit;
