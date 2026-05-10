<?php

    class UpdateRecoveryInformationsHandler {
        
        /**
         * Processa a atualização do email de recuperação do usuário autenticado.
         *
         * Recebe o novo email de recuperação, valida a sessão do usuário através do token
         * e atualiza o valor no banco de dados após verificar sua validade.
         *
         * Fluxo:
         * 1. Valida o token de autenticação (accountToken)
         * 2. Busca o usuário associado ao token
         * 3. Verifica se o campo obrigatório está presente no array de entrada
         * 4. Valida o tipo e integridade do email fornecido
         * 5. Verifica se o email possui formato válido
         * 6. Atualiza o email de recuperação na tabela users_config
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados.
         * @param array $data [emailRecovery => string]
         * Dados da requisição contendo:
         * - emailRecovery: novo email de recuperação do usuário
         *
         * @param string $accountToken Token de autenticação utilizado para identificar o usuário.
         *
         * @return array Retorna um array contendo:
         * - success (bool): status da operação
         * - message (string): mensagem descritiva
         * - action (string): ação a ser executada no frontend
         * - data (array|null): dados adicionais
         *
         * @throws void Interrompe a execução através de errorReport() em caso de:
         * - Sessão inválida (token não encontrado)
         *
         * @note O email informado deve estar em formato válido conforme FILTER_VALIDATE_EMAIL.
         */

        public function handle($mysqli, $data, $accountToken){
            valueVerification($accountToken, "string");

            $row = Database::query(
                $mysqli, 
                "SELECT user_id FROM remember_tokens WHERE token = ?", 
                true, 
                "s", 
                [$accountToken], 
                true
            );

            if ($row !== null) {
                verifyArrayFields($data, ["emailRecovery"]);

                $userId = $row["user_id"];
                $emailRecovery = $data["emailRecovery"];

                valueVerification($emailRecovery, "string");

                if(!filter_var($emailRecovery, FILTER_VALIDATE_EMAIL)){
                    return [
                        "success" => false,
                        "message" => "Email inválido.",
                        "action" => "SHOW_ERROR",
                        "data" => null
                    ];
                }

                Database::query(
                    $mysqli, 
                    "UPDATE users_config SET email_recovery = ? WHERE user_id = ?", 
                    false, 
                    "si", 
                    [$emailRecovery, $userId]
                );

                return [
                    "success" => true,
                    "message" => "Dados atualizados com sucesso!",
                    "action" => "SHOW_SUCCESS",
                    "data" => null
                ];
            } 
            
            else {
                errorReport(400, "Sessão inválida!");
            }
        }

    }