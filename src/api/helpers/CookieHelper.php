<?php

header("Access-Control-Allow-Credentials: true");
require "../../../bootstrap.php";

$data = getJson();

$cookieType = $data["type"];

valueVerification($cookieType);

if ($cookieType === "Jwt") {
    $haveCookie = isset($_COOKIE["JWTCookie"]);

    if (!$haveCookie) {
        $ch1 = curl_init("http://localhost/EnergySaveProject/api/services/JwtService.php");
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch1, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode(["timeExp" => 15]));

        $JWTtoken = curl_exec($ch1);
        valueVerification($JWTtoken);

        setcookie("JWTCookie", $JWTtoken, [
            'path' => '/',
            'domain' => "localhost",
            "secure" => false,
            "httponly" => true,
            "samesite" => "Strict"
        ]);
    }
}

if ($cookieType === "sessionCookie") {
    $rememberChecked = $data["rememberChecked"];
    $rememberCookie = $data["rememberCookie"];
    $email = $data["email"];

    $mysqli = Database::connect();

    if ($mysqli->error) {
        http_response_code(502);
        die("Falha ao conectar ao banco de dados" . $mysqli->error);
    }

    $userData = Database::query($mysqli, "SELECT id FROM user WHERE email = ?", true, "s", [$email]);
    $userId = $userData["id"];

    SessionService::createSessionCookie($rememberChecked, $rememberCookie, $userId, $data, false);

}
