<?php 

    class UserSessions {

        /**
         * Valida o token de sessão, retorna um JSON com as sessões ativas do usuário, incluindo informações do dispositivo e localização.
         * Cada sessão inclui um campo "is_current" para indicar a sessão ativa atual. Remove sessões expiradas automaticamente.
         */

        public static function getSessions() : string {
            $mysqli = Database::connect();

            if(!isset($_COOKIE["rememberCookie"])){
                errorReport(400, "Sessão Inválida");
            }

            $session_cookie = $_COOKIE["rememberCookie"];
            valueVerification($session_cookie);

            $query_user = Database::query(
                $mysqli, 
                "SELECT user_id FROM remember_tokens WHERE token = ?",
                true,
                "s",
                [$session_cookie],
                true
            );

            $userId = $query_user["user_id"];
            if($userId === null){
                errorReport(400, "Sessão Inválida");
            }

            $stmt = $mysqli->prepare("SELECT * FROM user_sessions WHERE user_id = ?");
            if (!$stmt) {
                errorReport(500, "Erro ao preparar query");
            }

            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();
            $sessions = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

            $currentSession = json_decode(UserService::getDeviceInfo(), true);

            if (
                !$currentSession || !isset(
                    $currentSession["ip"],
                    $currentSession["sistema_operacional"],
                    $currentSession["origem_login"],
                    $currentSession["tipo_dispositivo"]
                )
            ) {
                errorReport(500, "Erro ao obter sessão atual");
            }

            foreach ($sessions as &$session) {
                $session["is_current"] = (
                    $session["ip"] === $currentSession["ip"] &&
                    $session["sistema_operacional"] === $currentSession["sistema_operacional"] &&
                    $session["origem_login"] === $currentSession["origem_login"] &&
                    $session["tipo_dispositivo"] === $currentSession["tipo_dispositivo"]
                );
            }

            $mysqli->close();
            return json_encode($sessions);            
        }

        /**
         * Revoga uma sessão específica do usuário.
         * Caso o parâmetro $data não for preenchido, ele irá revogar a sessão ativa do 
         * usuário.
         *
         * @param array|null $data Dados contendo o ID da sessão a ser revogada
         * @return void
         */

        public static function revokeSession(?array $data = null){
            $mysqli = Database::connect();

            if (!isset($_COOKIE["rememberCookie"])) {
                errorReport(401, "Sessão inválida");
            }

            $session_cookie = $_COOKIE["rememberCookie"];
            valueVerification($session_cookie);

            $logged_user = Database::query(
                $mysqli,
                "SELECT id, user_id FROM remember_tokens WHERE token = ?",
                true,
                "s",
                [$session_cookie],
                true
            );

            if (!$logged_user) {
                errorReport(401, "Sessão inválida");
            }

            $logged_userId = $logged_user["user_id"];
            $current_token_id = $logged_user["id"];

            if ($data === null) {

                // pega a sessão atual via token
                $session_row = Database::query(
                    $mysqli,
                    "SELECT id FROM user_sessions WHERE remember_token_id = ?",
                    true,
                    "i",
                    [$current_token_id]
                );

                if (!$session_row) {
                    errorReport(404, "Sessão não encontrada");
                }

                $session_id = $session_row["id"];

            } 
            
            else {

                if (!isset($data["id"])) {
                    errorReport(400, "ID inválido");
                }

                $session_id = $data["id"];
                valueVerification($session_id);

                $session_data = Database::query(
                    $mysqli,
                    "SELECT us.remember_token_id, rt.user_id
                    FROM user_sessions us
                    JOIN remember_tokens rt ON rt.id = us.remember_token_id
                    WHERE us.id = ?",
                    true,
                    "i",
                    [$session_id],
                    true
                );

                if (!$session_data) {
                    errorReport(404, "Sessão não encontrada");
                }

                if (!hash_equals((string)$logged_userId, (string)$session_data["user_id"])) {
                    errorReport(403, "Não autorizado");
                }

                $current_token_id = $session_data["remember_token_id"];
            }

            // Deleta sessão
            Database::query(
                $mysqli,
                "DELETE FROM user_sessions WHERE id = ?",
                false,
                "i",
                [$session_id]
            );

            // Deleta token
            Database::query(
                $mysqli,
                "DELETE FROM remember_tokens WHERE id = ?",
                false,
                "i",
                [$current_token_id]
            );

            // Se for a sessão atual remove cookie
            if ($data === null) {
                setcookie("rememberCookie", "", [
                    "expires" => time() - 3600,
                    "path" => "/",
                    "secure" => true,
                    "httponly" => true,
                    "samesite" => "Strict"
                ]);

                unset($_COOKIE["rememberCookie"]);
            }

            $mysqli->close();
        }

        /**
         * Revoga todas as sessões do usuário exceto a atual.
         *
         * @return void
         */

        public static function revokeAll(){
            $session_token = $_COOKIE["rememberCookie"] ?? null;
            valueVerification($session_token);
            
            $mysqli = Database::connect();   

            self::revokeOtherSessions($mysqli, $session_token);

            $mysqli->close();
        }

        /**
         * Revoga outras sessões do usuário exceto a sessão atual.
         *
         * @param mysqli $mysqli Conexão com o banco de dados
         * @param string $session_token Token da sessão atual
         * @return void
         */

        public static function revokeOtherSessions(mysqli $mysqli, string $session_token){
            $user_row = Database::query($mysqli, "SELECT user_id FROM remember_tokens WHERE token = ?", true, "s", [$session_token], true);

            verifyArrayFields($user_row, ["user_id"]);
            $user_id = $user_row["user_id"];

            Database::query(
                $mysqli,
                "DELETE FROM remember_tokens WHERE token <> ? AND user_id = ?",
                false,
                "si",
                [$session_token, $user_id]
            );

            Database::query(
                $mysqli,
                "DELETE FROM user_sessions 
                WHERE remember_token_id NOT IN (
                    SELECT id FROM remember_tokens WHERE user_id = ?
                )",
                false,
                "i",
                [$user_id]
            );
        }

        /**
         * Obtém informações do dispositivo do usuário logado e salva no 
         * banco de dados em user_sessions
         * @param string $client_id Query Id do cliente (Foreign Key)
         * @param int $remember_token_id Identificador único do token
         * @return void
         */  

        public static function saveSession(string $client_id, int $remember_token_id) {

            $mysqli = Database::connect();

            // Pega o IP do usuário
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR']
                ?? null;

            if ($ip && strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Inicializando as variávis
            $sistema_operacional = null;
            $origem_login = null;
            $tipo_dispositivo = null;

            // Formatando e pegando o sistema operacional
            if ($userAgent) {

                if (preg_match('/windows nt/i', $userAgent)) $sistema_operacional = 'Windows';
                elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $sistema_operacional = 'Mac';
                elseif (preg_match('/linux/i', $userAgent) && !preg_match('/android/i', $userAgent)) $sistema_operacional = 'Linux';
                elseif (preg_match('/android/i', $userAgent)) $sistema_operacional = 'Android';
                elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $sistema_operacional = 'iOS';

                if (preg_match('/ipad|tablet/i', $userAgent)) $tipo_dispositivo = 'Tablet';
                elseif (preg_match('/mobile/i', $userAgent) && preg_match('/android|iphone/i', $userAgent)) $tipo_dispositivo = 'Mobile';
                else $tipo_dispositivo = 'Desktop';

                if (preg_match('/edg/i', $userAgent)) $origem_login = 'Edge';
                elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) $origem_login = 'Chrome';
                elseif (preg_match('/firefox/i', $userAgent)) $origem_login = 'Firefox';
                elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) $origem_login = 'Safari';
            }

            $localizacao = null;
            date_default_timezone_set('America/Sao_Paulo');
            $agora = date("Y-m-d H:i:s");

            $existing = Database::query(
                $mysqli,
                "SELECT id FROM user_sessions 
                WHERE remember_token_id = ?",
                true,
                "i",
                [$remember_token_id]
            );

            if ($existing && isset($existing["id"])) {

                Database::query(
                    $mysqli,
                    "UPDATE user_sessions 
                    SET primeiro_login = ? 
                    WHERE id = ?",
                    false,
                    "si",
                    [$agora, $existing["id"]]
                );

            } 

            else {
                Database::query(
                    $mysqli,
                    "INSERT INTO user_sessions 
                    (user_id, remember_token_id, ip, sistema_operacional, origem_login, tipo_dispositivo, localizacao, primeiro_login) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    false,
                    "iissssss",
                    [$client_id, $remember_token_id, $ip, $sistema_operacional, $origem_login, $tipo_dispositivo, $localizacao, $agora]
                );
            }

            $mysqli->close();
        }

        /**
         * Verifica se o usuário está logado corretamente no sistema.
         * Além de retornar um booleano com a resposta, ele verifica a veracidade do token, verificando
         * seu tempo de expiração e se ele é válido.
         *
         */

        public static function isLoggedIn() : bool {
            return UserService::getAuthenticatedUser() !== null;
        }

    }