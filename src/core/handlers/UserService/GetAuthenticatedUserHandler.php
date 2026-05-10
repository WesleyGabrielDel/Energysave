<?php 

    class GetAuthenticatedUserHandler {
        
        /**
         * Obtém os dados do usuário autenticado a partir do token de sessão (rememberCookie).
         *
         * Valida o token presente no cookie, verifica sua expiração e,
         * se válido, retorna os dados do usuário associados a ele.
         * Caso o token esteja expirado, a sessão é revogada automaticamente.
         *
         * Fluxo:
         * 1. Verifica se o token foi fornecido como parâmetro
         * 2. Caso não, tenta obter o token a partir do cookie "rememberCookie"
         * 3. Retorna null se nenhum token estiver disponível
         * 4. Consulta o banco para validar o token e obter o user_id
         * 5. Verifica se o token está expirado
         * 6. Se expirado, revoga a sessão e retorna null
         * 7. Se válido, busca os dados do usuário no banco
         * 8. Retorna os dados do usuário em formato JSON
         *
         * @param string|null $rememberCookie Token de autenticação do usuário.
         * - Se null, será utilizado o valor do cookie "rememberCookie"
         *
         * @return string|null JSON contendo:
         * - id (int): ID do usuário
         * - nome (string): Nome do usuário
         * - email (string): Email do usuário
         * - dataCadastro (string): Data de cadastro
         * - profile_picture (string|null): URL da foto de perfil
         * 
         * Retorna null nos casos:
         * - Token inexistente
         * - Token inválido
         * - Token expirado
         *
         * @note Tokens expirados são automaticamente removidos e invalidados.
         * @note O método depende diretamente do cookie "rememberCookie" quando nenhum parâmetro é fornecido.
         */

        public function handle(?string $rememberCookie = null): ?string {

            if ($rememberCookie === null) {
                if (!isset($_COOKIE["rememberCookie"])) return null;
                $rememberCookie = $_COOKIE["rememberCookie"];
            }

            $mysqli = Database::connect();

            $res = Database::query(
                $mysqli,
                "SELECT user_id, exp FROM remember_tokens WHERE token = ?",
                true,
                "s",
                [$rememberCookie]
            );

            if ($res) {
                // Se o token estiver expirado
                if ($res["exp"] < time()) {
                    UserService::revokeLogIn($mysqli, $rememberCookie);
                    $mysqli->close();
                    return null;
                }

                // Se ele for válido
                $userData = Database::query(
                    $mysqli,
                    "SELECT id, nome, email, dataCadastro, profile_picture FROM user WHERE id = ?",
                    true,
                    "i",
                    [$res["user_id"]]
                );

                $mysqli->close();

                return json_encode($userData);
            }

            $mysqli->close();
            return null;
        }

    }