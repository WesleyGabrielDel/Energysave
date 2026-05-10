<?php  

    class TwoFactorAuthService {

        public static function createCode(mysqli $mysqli, string $email){
            $code = strval(rand(100000, 999999));
            $expires = time() + 300;
            
            $code_row = Database::query(
                $mysqli,
                "SELECT time_exp FROM email_codes WHERE email = ?",
                true,
                "s",
                [$email]
            );

            if(empty($code_row)){
                Database::query(
                    $mysqli,
                    "INSERT INTO email_codes (email, codigo, time_exp) VALUES (?, ?, ?)",
                    false,
                    "sss",
                    [$email, $code, $expires]
                );
            }

            else if ($code_row["time_exp"] < time()) {
                Database::query(
                    $mysqli,
                    "UPDATE email_codes 
                    SET codigo = ?, time_exp = ?
                    WHERE email = ?",
                    false,
                    "sss",
                    [$code, $expires, $email]
                );
            }

            else {
                return false;
            }

            return $code;
        }

        public static function verifyCode(mysqli $mysqli, array $row, string $email){

            if ($row["time_exp"] < time()) {

                Database::query(
                    $mysqli,
                    "DELETE FROM email_codes WHERE email = ?",
                    false,
                    "s",
                    [$email]
                );

                $code = TwoFactorAuthService::createCode($mysqli, $email);
                return EmailSender::sendEmail($mysqli, $email, ["type" => "cadastro", "code" => $code]);
            }

            return [
                "success" => true,
                "message" => "Código ativo ainda válido!",
                "action" => "GO_TO_CODE_PAGE",
                "data" => ["goToCodePage" => true]
            ];
        }

        public static function needsNewCode(string $email, mysqli $mysqli){
            $code_row = Database::query(
                $mysqli,
                "SELECT codigo, time_exp FROM email_codes WHERE email = ?",
                true,
                "s",
                [$email]
            );

            if(empty($code_row)){
                return true;
            }

            if ($code_row["time_exp"] < time()) {
                Database::query(
                    $mysqli,
                    "DELETE FROM email_codes WHERE email = ?",
                    false,
                    "s",
                    [$email]
                );

                return true;
            }

            return false;
        }

    }