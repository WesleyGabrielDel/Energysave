<?php 

    class SessionService {

        /**
         * Cria e gerencia cookies de sessão "lembrar de mim".
         * Baseado na opção do usuário, cria/renova tokens no banco e cookies no navegador (1-30 dias).
         * Lida com expiração, renovação e limpeza de tokens inválidos.
         *
         * @param string $rememberChecked "on" se "lembrar-me" marcado
         * @param bool $rememberCookie Se já existe cookie no navegador
         * @param mysqli $mysqli Conexão com o banco
         * @param int $userId ID do usuário
         * @param array $data Dados adicionais (ex.: token existente)
         * @param bool $isSecure Se o cookie deve ser seguro (HTTPS)
         * @return array Dados do token criado ou renovado
         */

        public static function createSessionCookie($rememberChecked, $rememberCookie, $userId, $data, $isSecure) {
            $days = ($rememberChecked === "on") ? 30 : 1;

            $mysqli = Database::connect();

            if (!$rememberCookie) {
                return self::createRememberToken($mysqli, $userId, $days, $isSecure);
            }

            $rememberCookieToken = $data["rememberCookieToken"];

            $row = self::getRememberToken($mysqli, $rememberCookieToken);

            if ($row) {

                if (self::isExpired($row["exp"])) {
                    self::deleteRememberToken($mysqli, $rememberCookieToken);
                    return self::createRememberToken($mysqli, $userId, $days, $isSecure);
                }

                self::setRememberCookie($rememberCookieToken, $row["exp"], $isSecure);

                return ["token_id" => self::getTokenId($mysqli, $rememberCookieToken)];
            }

            self::deleteRememberCookie();

            $mysqli->close();

            return self::createRememberToken($mysqli, $userId, $days, $isSecure);
        }

        /**
         * Valida o cookie de sessão e retira do navegador caso seja inválido ou esteja
         * expirado.
         * 
         * @param bool $isSecure Se o cookie deve ser seguro (HTTPS)
         * @return void 
         */   

        public static function validateSessionToken($isSecure) {
            $mysqli = Database::connect();

            if (!isset($_COOKIE["rememberCookie"])) {
                errorReport(401, "Sessão Inválida");
            }

            $session_token = $_COOKIE["rememberCookie"] ?? null;

            // Verifica se o token foi enviado
            valueVerification($session_token);

            // Busca o token no banco
            $row = self::getRememberToken($mysqli, $session_token);

            if (!$row) {
                self::deleteRememberCookie();
                $mysqli->close();
                return false;
            }

            // Verifica expiração
            if (self::isExpired($row["exp"])) {
                self::deleteRememberToken($mysqli, $session_token);
                self::deleteRememberCookie();

                $mysqli->close();
                return false;
            }

            // Token válido → renova cookie
            self::setRememberCookie($session_token, $row["exp"], $isSecure);

            $mysqli->close();
            return true;
        }

        #region Funções privadas 

        private static function createRememberToken($mysqli, $userId, $days, $isSecure) {
            $token = bin2hex(random_bytes(32));
            $expTime = time() + (86400 * $days);

            Database::query(
                $mysqli,
                "INSERT INTO remember_tokens (user_id, token, exp) VALUES (?, ?, ?)",
                false,
                "isi",
                [$userId, $token, $expTime]
            );

            $tokenId = $mysqli->insert_id;

            self::setRememberCookie($token, $expTime, $isSecure);

            return ["token" => $token, "exp" => $expTime, "token_id" => $tokenId];
        }

        private static function deleteRememberToken($mysqli, $token) {
            Database::query(
                $mysqli,
                "DELETE FROM remember_tokens WHERE token = ?",
                false,
                "s",
                [$token]
            );
        }

        private static function getRememberToken($mysqli, $token) {
            return Database::query(
                $mysqli,
                "SELECT token, exp FROM remember_tokens WHERE token = ?",
                true,
                "s",
                [$token]
            );
        }

        private static function setRememberCookie($token, $exp, $isSecure) {
            $secure = $isSecure && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            setcookie("rememberCookie", $token, [
                "expires" => $exp,
                "path" => "/",
                "secure" => $secure,
                "httponly" => true,
                "samesite" => "Strict"
            ]);
        }

        private static function deleteRememberCookie() {
            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            setcookie("rememberCookie", "", [
                "expires" => 0,
                "path" => "/",
                "secure" => $secure,
                "httponly" => true,
                "samesite" => "Strict"
            ]);
        }

        private static function isExpired($exp) {
            return $exp < time();
        }

        private static function getTokenId($mysqli, $token) {
            $res = Database::query(
                $mysqli,
                "SELECT id FROM remember_tokens WHERE token = ?",
                true,
                "s",
                [$token]
            );

            return $res["id"];
        }

        #endregion
    
    }