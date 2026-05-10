<?php

    class UpdatePasswordHandler {

        /**
         * Processa a alteração de senha do usuário autenticado.
         *
         * Recebe os dados de senha atual, nova senha e confirmação, valida a sessão
         * do usuário através do token e realiza a atualização da senha no banco de dados.
         * Após a alteração, invalida outras sessões ativas por segurança.
         *
         * Fluxo:
         * 1. Valida o token de autenticação (accountToken)
         * 2. Busca o usuário associado ao token
         * 3. Verifica se os campos obrigatórios estão presentes no array de entrada
         * 4. Valida o tipo e integridade das senhas fornecidas
         * 5. Verifica se a nova senha coincide com a confirmação
         * 6. Verifica se a nova senha atende ao tamanho mínimo (8 caracteres)
         * 7. Recupera a senha atual do usuário no banco
         * 8. Valida a senha atual utilizando password_verify
         * 9. Gera o hash da nova senha
         * 10. Atualiza a senha no banco de dados
         * 11. Atualiza a data de alteração de senha em users_config
         * 12. Revoga todas as outras sessões ativas do usuário
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados.
         * @param array $data [currentPassword => string, newPassword => string, confirmPassword => string]
         * Dados da requisição contendo:
         * - currentPassword: senha atual do usuário
         * - newPassword: nova senha desejada
         * - confirmPassword: confirmação da nova senha
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
         * @note Por segurança, todas as outras sessões do usuário são invalidadas após a troca de senha.
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

            if ($row === null) {
                errorReport(400, "Sessão inválida!");
            }

            verifyArrayFields($data, ["currentPassword", "newPassword", "confirmPassword"]);

            $userId = $row["user_id"];

            $currentPassword = $data["currentPassword"];
            $newPassword = $data["newPassword"];
            $confirmPassword = $data["confirmPassword"];

            valueVerification($currentPassword, "string");
            valueVerification($newPassword, "string");
            valueVerification($confirmPassword, "string");

            if ($newPassword !== $confirmPassword) {
                return [
                    "success" => false,
                    "message" => "As senhas não coincidem!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            if (strlen($newPassword) < 8) {
                return [
                    "success" => false,
                    "message" => "A senha deve ter ao menos 8 caracteres!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $user = Database::query(
                $mysqli,
                "SELECT senha, email FROM user WHERE id = ?",
                true,
                "i",
                [$userId],
                true
            );

            if ($user === null || !password_verify($currentPassword, $user["senha"])) {
                return [
                    "success" => false,
                    "message" => "Credenciais inválidas",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            Database::query(
                $mysqli,
                "UPDATE user SET senha = ? WHERE id = ?",
                false,
                "si",
                [$newHash, $userId]
            );

            Database::query(
                $mysqli,
                "UPDATE users_config SET password_change = NOW() WHERE user_id = ?",
                false,
                "i",
                [$userId]
            );

            UserSessions::revokeOtherSessions($mysqli, $accountToken);

            return [
                "success" => true,
                "message" => "Senha atualizada com sucesso!",
                "action" => "SHOW_SUCCESS",
                "data" => null
            ];
        }

    }