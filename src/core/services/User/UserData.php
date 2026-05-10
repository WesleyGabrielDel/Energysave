<?php 
    // Dependências
    require_once CONTROLLER_CORE_PATH . "handlers/UserData/UpdateUserDataHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserData/UpdateAvatarHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserData/UpdatePasswordHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserData/UpdatePreferencesHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserData/UpdateRecoveryHandler.php";

    class UserData {

        /**
         * Obtém as informações do usuário logado.
         * Caso o parâmetro $info esteja preenchido, ele irá retornar apenas as informações
         * desejadas.
         *
         * @param array $info Informações específicas a serem retornadas (nome, email, telefone, etc)
         * @return void
         */

        public static function getInfo($info = null){
            $accountToken = $_COOKIE["rememberCookie"] ?? null;
            
            $mysqli = Database::connect();
            $row = Database::query($mysqli, "SELECT token, user_id FROM remember_tokens WHERE token = ?", true, "s", [$accountToken]);

            $data = [];

            // Se houver uma sessão salva no banco
            if($row !== null){
                $userId = $row["user_id"];

                /* Pegando as informações do usuário no banco */
                $userInfo = 
                Database::query
                (
                    $mysqli,
                    "SELECT u.id, u.nome, u.email, u.profile_picture, u.dataCadastro, u.data_nascimento,
                            c.genero, c.idioma,
                            c.push, c.push_pico, c.push_dicas, c.push_resumo, c.push_sistema,
                            c.relatorios_email,
                            c.two_factor_enabled,
                            c.email_recovery, c.phone_recovery, c.password_change
                    FROM user u
                    LEFT JOIN users_config c ON c.user_id = u.id
                    WHERE u.id = ?",
                    true,
                    "i",
                    [$userId]
                );
                
                $data = [
                    // =========================
                    // INFORMAÇÕES PESSOAIS
                    // =========================
                    "nome" => $userInfo["nome"],
                    "genero" => $userInfo["genero"] ?? "nao-informado",
                    "email" => $userInfo["email"],
                    "telefone" => $userInfo["phone_recovery"] ?? "",
                    "criado" => date("Y-m-d H:i:s", strtotime($userInfo["dataCadastro"])),
                    "nascimento" => $userInfo["data_nascimento"] ?? "",
                    "idioma" => $userInfo["idioma"] ?? "pt-br",
                    "avatar" => $userInfo["profile_picture"] ?? BASE_URL . "public/images/avatars/logo-conta.png",

                    // =========================
                    // NOTIFICAÇÕES PUSH
                    // =========================
                    "push" => (bool)($userInfo["push"] ?? true),
                    "pushPico" => (bool)($userInfo["push_pico"] ?? true),
                    "pushDicas" => (bool)($userInfo["push_dicas"] ?? true),
                    "pushResumo" => (bool)($userInfo["push_resumo"] ?? true),
                    "pushSistema" => (bool)($userInfo["push_sistema"] ?? true),

                    // =========================
                    // NOTIFICAÇÕES EMAIL
                    // =========================
                    "relatoriosEmail" => (bool)($userInfo["relatorios_email"] ?? true),

                    // =========================
                    // SEGURANÇA
                    // =========================
                    "twoFA" => (bool)($userInfo["two_factor_enabled"] ?? false),
                    "emailRecovery" => $userInfo["email_recovery"] ?? "",
                    "phoneRecovery" => $userInfo["phone_recovery"] ?? "",
                    "password_change" => $userInfo["password_change"] ?? ""
                ];

                if(is_array($info) && !empty($info)){
                    $data = array_intersect_key($data, array_flip($info));
                }
            }

            else {
                errorReport(400, "Não foi possível resgatar as informações do usuário!");
            }

            $mysqli->close();

            echo json_encode($data);            
        }

        /**
         * Atualiza os dados do usuário logado.
         *
         * @param array $data Dados a serem atualizados (type, nome, telefone, etc)
         * @return void
         */

        public static function updateData($data){
            
            verifyArrayFields($_COOKIE, ["rememberCookie"]);

            $accountToken = $_COOKIE["rememberCookie"] ?? null;
            $type = $data["type"] ?? null;

            $mysqli = Database::connect();

            switch ($type) {
                case "personal":
                    Validators::validateName($data["nome"]);
                    Validators::validateBirthData($data["nascimento"]);
                    Validators::validatePhone($data["telefone"]);

                    $status = (new UpdatePersonalInformationsHandler())->handle($mysqli, $data, $accountToken);
                    break;

                case "recovery":
                    $status = (new UpdateRecoveryInformationsHandler())->handle($mysqli, $data, $accountToken);
                    break;

                case "password":
                    $status = (new UpdatePasswordHandler())->handle($mysqli, $data, $accountToken);
                    break;

                case "toggle":
                    $status = (new UpdatePreferencesHandler())->handle($mysqli, $data, $accountToken);
                    break;

                case "avatar":
                    $status = (new UpdateAvatarHandler())->handle($mysqli, $data, $accountToken);
                    break;

                default:
                    $status = [
                        "success" => false,
                        "message" => "Tipo de atualização inválido",
                        "action" => "SHOW_ERROR",
                        "data" => null
                    ];
                    break;
            }

            $mysqli->close();
            echo json_encode($status);
        }

    
}