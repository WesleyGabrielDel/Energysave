<?php 

    require_once CONTROLLER_CORE_PATH . "handlers/UserService/GetDeviceInfoHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserService/GetAuthenticatedUserHandler.php";
    require_once CONTROLLER_CORE_PATH . "handlers/UserService/DeleteAccountHandler.php";

    class UserService {

        /**
         * Obtém informações do dispositivo do usuário logado e salva no 
         * banco de dados em user_sessions
         * 
         * @return string|false JSON com as informações do dispositivo do usuário (IP, sistema operacional, origem do login, tipo de dispositivo e localização)
         */  

        public static function getDeviceInfo() : ?string {
            return (new GetDeviceInfoHandler())->handle();
        }

        /**
         * Obtém informações do usuário logado via cookie de sessão.
         * Valida o token no banco, retorna dados do usuário em JSON ou null se inválido/expirado.
         * Remove tokens expirados automaticamente.
         *
         * @param string|null $rememberCookie Token do cookie (usa $_COOKIE se null)
         * @return string|null JSON com dados do usuário ou null
         */

        public static function getAuthenticatedUser(?string $rememberCookie = null) : ?string {
            return (new GetAuthenticatedUserHandler())->handle($rememberCookie);
        }

        /**
         * Exclui a conta do usuário logado e todos os dados relacionados.
         *
         * @return void
         */

        public static function deleteAccount(){
            // Ainda não implementado, mas irei fazer.
        }

        /**
         * Revoga a sessão do usuário, retirando seu token de sessão do navegador e do banco de dados.
         *
         * @param mysqli $mysqli Conexão com o banco
         * @param string $session_cookie Valor do cookie de sessão
         * @return void
         */

        public static function revokeLogIn(mysqli $mysqli, string $session_cookie){
            Database::query($mysqli, "DELETE FROM remember_tokens WHERE token = ?", false, "s", [$session_cookie]);
            setcookie("rememberCookie", "", [
                "expires" => time() - 3600,
                "path" => "/",
                "secure" => true,
                "httponly" => true,
                "samesite" => "Strict"
            ]);

            unset($_COOKIE["rememberCookie"]);
        }
        
    }