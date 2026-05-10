<?php

    class GoogleAuthHandler {

        /**
         * Processa a autenticação do usuário via Google.
         *
         * Realiza a validação do token ID (id_token) fornecido pelo Google, consultando
         * a API oficial para obter os dados do usuário. Após validar integridade, expiração
         * e origem do token, decide entre:
         *
         * - Login do usuário (caso já possua conta vinculada)
         * - Vinculação da conta Google a um usuário existente (quando $cadastro = true)
         *
         * Fluxo:
         * 1. Extrai o token e o indicador de cadastro do array de entrada
         * 2. Consulta a API do Google para validar o token
         * 3. Verifica validade, expiração e integridade dos dados retornados
         * 4. Valida campos obrigatórios (email_verified, aud, sub, iss)
         * 5. Direciona para login ou vinculação de conta
         *
         * @param array $data [token => string, cadastro => bool] 
         * Dados da requisição, onde:
         * - token: ID Token JWT fornecido pelo Google
         * - cadastro: indica se deve vincular a conta (true) ou apenas autenticar (false)
         *
         * @return array Retorna um array no padrão da aplicação contendo:
         * - success (bool): status da operação
         * - message (string): mensagem descritiva
         * - action (string|null): ação a ser executada no frontend
         * - data (array|null): dados adicionais (ex: login, email, redirect, etc)
         *
         * @throws void Interrompe a execução através de errorReport() em caso de:
         * - Token inválido ou ausente
         * - Token expirado
         * - Dados obrigatórios ausentes ou inconsistentes
         * - Falha na validação de origem (aud, iss) ou integridade do usuário
         */

        public function handle($data) {

            // Extrai o token do Google e a indicação de cadastro do array de entrada
            $token = $data["token"] ?? null;
            $cadastro = $data["cadastro"] ?? false;
            $user = HttpClient::get("https://oauth2.googleapis.com/tokeninfo?id_token=" . $token);

            // Verifica se os dados necessários estão presentes e são válidos
            valueVerification($user);
            valueVerification($token);
            valueVerification($token);

            // Verifica se o token está expirado
            if ($user["exp"] < time()) {
                errorReport(401, "Token já expirado!");
            }

            // Verifica se as informações necessárias do usuário estão presentes e são válidas
            if (!isset($user["email_verified"], $user["aud"], $user["sub"], $user["iss"])) {
                errorReport(401, "Informações incorretas do usuário!");
            }

            if ($user["email_verified"] && $user["aud"] == "315083166922-la7c6stmrbkau0dbj35rpto887nd4tm2.apps.googleusercontent.com" &&  $user["sub"] && $user["iss"] == "https://accounts.google.com") {
                if ($cadastro) {
                    return $this->createAccount($user);
                } 
                
                else {
                    return $this->login($user);
                }
            }

            errorReport(401, "Informações do usuário incorretas!");
        }

        private function login(mixed $user) {
            $mysqli = Database::connect();

            $stmt = Database::query($mysqli, "SELECT id FROM user WHERE email = ?", false, "s", [$user["email"]]);

            if ($stmt->num_rows == 0) {
                $stmt->close();
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Crie sua conta para que seja possível realizar o login!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $stmt->close();

            $row = Database::query($mysqli, "SELECT sub FROM user WHERE email = ?", true, "s", [$user["email"]]);
            $userSub = $row["sub"];

            if ($userSub === null) {
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Vincule sua conta ao Google para que seja possível fazer o login por este método!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            if (!hash_equals($userSub, $user["sub"])) {
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Valor incorreto recebido",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $mysqli->close();

            return [
                "success" => true,
                "message" => "Login pelo google efetuado com sucesso!",
                "action" => "REDIRECT",
                "data" => [
                    "to" => "/home",
                    "login" => true,
                    "email" => $user["email"],
                    "changeLocation" => true
                ]
            ];
        }

        private function createAccount(mixed $user) {

            $mysqli = Database::connect();

            $stmt = Database::query($mysqli, "SELECT id FROM user WHERE email = ?", false, "s", [$user["email"]]);
            // Caso o usuário não tenha criado uma conta, ele cria. 
            if ($stmt->num_rows == 0) {
                $defaultAvatar = BASE_URL . "public/images/avatars/logo-conta.png";

                Database::query(
                    $mysqli,
                    "INSERT INTO user (nome, email, sub, profile_picture) VALUES (?, ?, ?, ?)",
                    false,
                    "ssss",
                    [$user["name"], $user["email"], $user["sub"], $defaultAvatar]
                );

                $row = Database::query(
                    $mysqli,
                    "SELECT id FROM user WHERE email = ?",
                    true,
                    "s",
                    [$user["email"]],
                    true
                );

                $user_id = $row["id"];

                Database::query(
                    $mysqli, 
                    "INSERT INTO users_config (user_id) VALUES (?)", 
                    false, 
                    "i", 
                    [$user_id]
                );

                return [
                    "success" => true,
                    "message" => "Usuário criado com sucesso!",
                    "action" => "",
                    "user_id" => $user_id,
                    "data" => ["changeLocation" => "configPage"]
                ];
            }

            else {
                return [
                    "success" => false,
                    "message" => "Usuário já cadastrado!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }
        }
        
    }