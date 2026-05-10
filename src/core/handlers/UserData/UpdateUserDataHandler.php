<?php

    class UpdatePersonalInformationsHandler {
        
        /**
         * Processa a atualização das informações pessoais do usuário autenticado.
         *
         * Recebe os dados opcionais do usuário, valida a sessão através do token
         * e atualiza as informações pessoais nas tabelas correspondentes no banco de dados.
         *
         * Fluxo:
         * 1. Valida o token de autenticação (accountToken)
         * 2. Busca o usuário associado ao token
         * 3. Extrai os dados opcionais do array de entrada
         * 4. Valida o tipo e integridade dos campos fornecidos (se presentes)
         * 5. Atualiza os dados básicos na tabela user (nome e data de nascimento)
         * 6. Atualiza os dados complementares na tabela users_config (gênero, idioma, telefone)
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados.
         * @param array $data [nome => string|null, telefone => string|null, nascimento => string|null, genero => string|null, idioma => string|null]
         * Dados da requisição contendo:
         * - nome: nome do usuário
         * - telefone: telefone de recuperação
         * - nascimento: data de nascimento
         * - genero: gênero do usuário
         * - idioma: idioma preferido
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
         * @note Todos os campos são opcionais e apenas os valores fornecidos são considerados na atualização.
         */

        public function handle($mysqli, $data, $accountToken) {
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
                return [
                    "success" => false,
                    "message" => "Sessão inválida!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            if ($row !== null) {
                $userId = $row["user_id"];

                $nome = $data["nome"] ?? null;
                $telefone = $data["telefone"] ?? null;
                $nascimento = $data["nascimento"] ?? null;
                $genero = $data["genero"] ?? null;
                $idioma = $data["idioma"] ?? null;

                if ($nome !== null) valueVerification($nome, "string");
                if ($telefone !== null) valueVerification($telefone, "string");
                if ($nascimento !== null) valueVerification($nascimento, "string");
                if ($genero !== null) valueVerification($genero, "string");
                if ($idioma !== null) valueVerification($idioma, "string");

                Database::query(
                    $mysqli,
                    "UPDATE user SET nome = ?, data_nascimento = ? WHERE id = ?",
                    false,
                    "ssi",
                    [$nome, $nascimento, $userId]
                );

                Database::query(
                    $mysqli,
                    "UPDATE users_config SET genero = ?, idioma = ?, phone_recovery = ? WHERE user_id = ?",
                    false,
                    "sssi",
                    [$genero, $idioma, $telefone, $userId]
                );

                return [
                    "success" => true,
                    "message" => "Dados atualizados com sucesso!",
                    "action" => "SHOW_SUCCESS",
                    "data" => null
                ];
            } 
            
        }

    }