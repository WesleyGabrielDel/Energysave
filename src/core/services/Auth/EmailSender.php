<?php 

    class EmailSender {

        /**
         * Envia um email através de um serviço externo (servidor Python).
         *
         * Essa função verifica se o servidor Python está ativo antes de realizar o envio.
         * Caso esteja offline ou ocorra falha na requisição, retorna erro.
         * Também permite configurar opções adicionais como tipo de email, código e dados extras.
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados (será fechada ao final da execução).
         * @param string $email Email do destinatário.
         * @param array $options Parâmetros opcionais para o envio.
         * 
         * Opções disponíveis em $options:
         * @param string $options["type"] Tipo do email (ex: "cadastro", "recuperacao").
         * @param string $options["code"] Código opcional a ser enviado no email.
         * @param mixed  $options["data"] Dados adicionais que podem ser enviados no payload.
         *
         * @return array{
         *     success: bool,
         *     message: string,
         *     action: string,
         *     data: mixed|null
         * }
         *
         * @example
         * EmailService::sendEmail($mysqli, "user@email.com", [
         *     "type" => "cadastro",
         *     "code" => "123456",
         *     "data" => ["nome" => "João"]
         * ]);
         */

        public static function sendEmail(mysqli $mysqli, string $email, $options = []){
            if(isset($options["type"])) { $emailType = $options["type"]; };

            $python_is_active = HttpClient::request(
                "GET",
                "http://127.0.0.1:5000/health",
                null,
                true
            );

            if ($python_is_active === false) {
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Falha ao chamar o servidor Python",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $payload = [
                "email" => $email,
                "type" => $emailType,
            ];

            if (isset($options["code"])) {
                $payload["code"] = $options["code"];
            }
            if (isset($options["data"])) {
                $payload["data"] = $options["data"];
            }

            $response = HttpClient::request(
                "POST",
                "http://127.0.0.1:5000/send-email",
                $payload,
                true
            );

            if ($response === false) {
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Falha ao chamar o servidor Python",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $mysqli->close();

            return [
                "success" => true,
                "message" => "Indo para a página de colocar o código!",
                "action" => "GO_TO_CODE_PAGE",
                "data" => ["goToCodePage" => true]
            ];
        }

    }