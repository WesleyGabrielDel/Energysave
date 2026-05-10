<?php 

    class SignUpHandler {
        
        /**
         * Processa o cadastro de um novo usuário.
         *
         * Realiza a validação dos dados de entrada, verifica se o email já está cadastrado
         * e controla o fluxo de verificação por código enviado ao email. Dependendo do estado,
         * pode iniciar o envio de um novo código ou validar um código já existente.
         *
         * Fluxo:
         * 1. Extrai email, senha e nome do array de entrada
         * 2. Valida presença e tipo dos dados
         * 3. Verifica se o email possui formato válido
         * 4. Consulta o banco para verificar se o usuário já existe
         * 5. Caso já exista:
         *    - Retorna erro de usuário já cadastrado
         * 6. Caso não exista:
         *    - Verifica se já há um código de verificação ativo para o email
         *        • Se existir: encaminha para validação do código
         *        • Se não existir: inicia envio de código por email
         *
         * @param array $data [email => string, password => string, name => string]
         * Dados da requisição, onde:
         * - email: endereço de email do usuário
         * - password: senha em texto plano
         * - name: nome do usuário
         *
         * @return array Retorna um array no padrão da aplicação contendo:
         * - success (bool): status da operação
         * - message (string): mensagem descritiva
         * - action (string|null): ação a ser executada no frontend
         * - data (array|null): dados adicionais (ex: instruções de verificação)
         *
         * @throws void Interrompe a execução através de errorReport() em caso de:
         * - Email em formato inválido
         *
         * @note Este método não finaliza diretamente o cadastro do usuário.
         * Ele depende da verificação de código enviada por email para concluir o processo.
         */

        public function handle($data){

            $email = $data["email"];
            $password = $data["password"];
            $name = $data["name"];

            valueVerification($email, "string");
            valueVerification($password);
            valueVerification($name, "string");

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                errorReport(400, "Email inválido inserido!");
            }

            $mysqli = Database::connect();

            $stmt = Database::query(
                $mysqli,
                "SELECT id FROM user WHERE email = ?",
                false,
                "s",
                [$email]
            );

            if ($stmt->num_rows > 0) {
                $stmt->close();
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Usuário já cadastrado!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $stmt->close();

            $row = Database::query(
                $mysqli,
                "SELECT time_exp FROM email_codes WHERE email = ?",
                true,
                "s",
                [$email]
            );

            if ($row) {
                return TwoFactorAuthService::verifyCode($mysqli, $row, $email);
            }

            $code = TwoFactorAuthService::createCode($mysqli, $email);
            return EmailSender::sendEmail($mysqli, $email, ["type" => "cadastro", "code" => $code]);
            
        }

        
    }