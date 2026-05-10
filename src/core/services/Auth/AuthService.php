<?php 
    // Dependências
    require_once CONTROLLER_CORE_PATH . "handlers/AuthService/GoogleAuthHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/AuthService/LoginHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/AuthService/SignUpHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/AuthService/ReSendCodeHandler.php";

    class AuthService {

        /**
         * Realiza o login do usuário utilizando email e senha.
         *
         * Valida os dados de entrada, processa a autenticação e, em caso de sucesso,
         * cria a sessão do usuário (cookie + persistência no banco).
         *
         * @param array $data [email => string, password => string, remember => bool|null] Dados de autenticação do usuário.
         * @return array Retorna o resultado do processamento no padrão da aplicação.
         */

        public static function login(array $data){

            AuthValidator::validateCredentials($data); // Valida os dados de email e senha usando o validador

            $res = (new LoginHandler())->handle($data); // Envia os dados para o handler de login processar a autenticação

            if(isset($res["data"]["login"]) && $res["data"]["login"]){
                // Cria o cookie de sessão e salva a sessão no banco se o login for bem-sucedido
                $userId = UserRepository::findIdByEmail($data["email"]);
                $tokenData = SessionService::createSessionCookie($data["remember"] ?? false, false, $userId["id"], [], true);

                UserSessions::saveSession($userId["id"], $tokenData["token_id"]);
            }

            else if(isset($res["2fa"])){
                $mysqli = Database::connect();

                $code = TwoFactorAuthService::createCode($mysqli, $data["email"]);
                EmailSender::sendEmail($mysqli, $data["email"], ["type" => "2fa", "code" => $code]);  

            }

            return $res; // Retorna o resultado do processamento 
        }

        /**
         * Realiza o cadastro de um novo usuário.
         *
         * Valida os dados de entrada e encaminha para o handler responsável por criar o usuário.
         *
         * @param array $data [email => string, password => string, name => string] Dados necessários para o cadastro.
         * @return array Retorna o resultado do processamento no padrão da aplicação.
         */

        public static function signUp(array $data){
            AuthValidator::validateCredentials($data);
            return (new SignUpHandler())->handle($data);
        }

        /**
         * Realiza a autenticação do usuário via Google.
         *
         * Encaminha os dados para o handler responsável por validar o token do Google
         * e processar o login ou vinculação da conta. Em caso de autenticação bem-sucedida,
         * cria a sessão do usuário (cookie + persistência no banco).
         *
         * @param array $data [token => string, cadastro => bool] Token ID do Google (id_token) e indicador para vinculação de conta.
         * @return array Retorna o resultado do processamento no padrão da aplicação.
         */

        public static function googleAuth(array $data) {
            $res = (new GoogleAuthHandler())->handle($data);

            if(isset($res["data"]["login"]) && $res["data"]["login"]){
                $userId = UserRepository::findIdByEmail($res["data"]["email"]);
                $tokenData = SessionService::createSessionCookie(true, false, $userId["id"], [], true);

                UserSessions::saveSession($userId["id"], $tokenData["token_id"]);
            }

            return $res;
        }

        /**
         * Realiza o logout do usuário.
         *
         * Remove os tokens de autenticação e invalida a sessão atual.
         *
         * @return array Retorna o resultado do processamento no padrão da aplicação.
         */

        public static function logout(){
            UserSessions::revokeSession();
            
            return [
                "success" => true, 
                "message" => "Logout efetuado com sucesso", 
                "action" => "LOGOUT", 
                "data" => null
            ];
        }

        public static function reSendCode(array $data){
            $mysqli = Database::connect();

            $res = (new ReSendCodeHandler())->handle($data, $mysqli); 

            return $res;
        }

    }