<?php 

    class ReSendCodeHandler {

        public function handle(array $data, mysqli $mysqli){
            if (empty($data["email"]) || empty($data["emailType"])) {
                return [
                    "success" => false,
                    "message" => "Email ou tipo de email inválido!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $email = $data["email"];
            $email_type = $data["emailType"];

            $code_row = Database::query(
                $mysqli,
                "SELECT codigo, time_exp FROM email_codes WHERE email = ?",
                true,
                "s",
                [$email]
            );

            // Se já tiver um código ativo, ele reutiliza e envia ao email
            if($code_row && $code_row["time_exp"] > time()){
                EmailSender::sendEmail(
                    $mysqli, 
                    $email, 
                    ["code" => $code_row["codigo"], "type" => $email_type]
                );
            }     

            // Caso o código tenha expirado, ele cria outro e evia ao email
            else if($code_row && $code_row["time_exp"] < time()){
                $code = TwoFactorAuthService::createCode($mysqli, $email);
                EmailSender::sendEmail(
                    $mysqli, 
                    $email, 
                    ["code" => $code, "type" => $email_type]
                );
            }

            // Caso não tenha, ele cria um novo e envia ao email 
            else {
                $code = TwoFactorAuthService::createCode($mysqli, $email);
                EmailSender::sendEmail(
                    $mysqli, 
                    $email, 
                    ["code" => $code, "type" => $email_type]
                );
            }

            return [
                "success" => true,
                "message" => "Código reenviado com sucesso!",
                "action" => "",
                "data" => null
            ];      

        }

    }