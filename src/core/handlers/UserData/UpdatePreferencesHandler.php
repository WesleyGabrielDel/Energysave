<?php

    class UpdatePreferencesHandler {

        /**
         * Processa a atualização das preferências do usuário autenticado.
         *
         * Recebe os dados de configuração do usuário, valida a sessão através do token
         * e atualiza as preferências relacionadas a notificações e segurança no banco de dados.
         *
         * Fluxo:
         * 1. Valida o token de autenticação (accountToken)
         * 2. Busca o usuário associado ao token
         * 3. Extrai e normaliza os valores das preferências (cast para inteiro)
         * 4. Atualiza as configurações do usuário na tabela users_config
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados.
         * @param array $data [push => int|bool, pushPico => int|bool, pushDicas => int|bool, pushResumo => int|bool, pushSistema => int|bool, relatoriosEmail => int|bool, twoFA => int|bool]
         * Dados da requisição contendo:
         * - push: ativa/desativa notificações gerais
         * - pushPico: notificações de pico de consumo
         * - pushDicas: notificações de dicas
         * - pushResumo: notificações de resumo
         * - pushSistema: notificações do sistema
         * - relatoriosEmail: envio de relatórios por email
         * - twoFA: ativação de autenticação em dois fatores
         *
         * @param string $accountToken Token de autenticação utilizado para identificar o usuário.
         *
         * @return array Retorna um array contendo:
         * - success (bool): status da operação
         * - message (string): mensagem descritiva
         * - action (string): ação a ser executada no frontend
         * - data (array|null): dados adicionais
         *
         * @throws void Pode interromper o fluxo ao retornar erro em caso de sessão inválida.
         *
         * @note Todos os valores são normalizados para inteiro (0 ou 1) antes da persistência.
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
                return [
                    "success" => false,
                    "message" => "Sessão inválida!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $userId = $row["user_id"];

            $push = (int)($data["push"] ?? 0);
            $pushPico = (int)($data["pushPico"] ?? 0);
            $pushDicas = (int)($data["pushDicas"] ?? 0);
            $pushResumo = (int)($data["pushResumo"] ?? 0);
            $pushSistema = (int)($data["pushSistema"] ?? 0);
            $relatoriosEmail = (int)($data["relatoriosEmail"] ?? 0);
            $twoFA = (int)($data["twoFA"] ?? 0);

            Database::query(
                $mysqli,
                "UPDATE users_config 
                SET 
                    push = ?, 
                    push_pico = ?, 
                    push_dicas = ?, 
                    push_resumo = ?, 
                    push_sistema = ?, 
                    relatorios_email = ?, 
                    two_factor_enabled = ?
                WHERE user_id = ?",
                false,
                "iiiiiiii",
                [$push, $pushPico, $pushDicas, $pushResumo, $pushSistema, $relatoriosEmail, $twoFA, $userId]
            );

            return [
                "success" => true,
                "message" => "Preferências atualizadas com sucesso!",
                "action" => "NONE",
                "data" => null
            ];
        }

    }