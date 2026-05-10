<?php 

    class LoginHandler {
        /**
         * Processa a autenticação do usuário utilizando email e senha.
         *
         * Realiza a validação dos dados de entrada, verifica a existência do usuário
         * no banco de dados e compara a senha fornecida com a senha armazenada (hash).
         * Em caso de sucesso, garante que não exista sessão ativa anterior e retorna
         * os dados necessários para criação de sessão no fluxo superior.
         *
         * Fluxo:
         * 1. Extrai email, senha e informações de "remember" do array de entrada
         * 2. Valida a presença e integridade dos dados
         * 3. Verifica se o usuário existe no banco pelo email informado
         * 4. Recupera o hash da senha armazenada
         * 5. Compara a senha fornecida com o hash usando password_verify
         * 6. Caso válido:
         *    - Revoga sessão ativa existente (se houver)
         *    - Retorna dados de sucesso para criação de sessão
         * 7. Caso inválido:
         *    - Retorna erro de credenciais incorretas
         *
         * @param array $data [email => string, password => string, remember => bool|null]
         * Dados da requisição, onde:
         * - email: endereço de email do usuário
         * - password: senha em texto plano fornecida pelo usuário
         * - remember: indica se o usuário deseja ser lembrado (opcional)
         *
         * @return array Retorna um array no padrão da aplicação contendo:
         * - success (bool): status da operação
         * - message (string): mensagem descritiva
         * - action (string|null): ação a ser executada no frontend
         * - data (array|null): dados adicionais, incluindo:
         *     - to (string): rota de redirecionamento
         *     - login (bool): indica sucesso no login
         *     - email (string): email do usuário autenticado
         *     - rememberChecked (bool|null): valor enviado pelo usuário
         *     - rememberCookie (bool): indica presença de cookie de "remember"
         *     - rememberCookieToken (string|null): token armazenado no cookie (se existir)
         *     - changeLocation (bool): indica redirecionamento no frontend
         *
         * @throws void Interrompe a execução através de valueVerification() em caso de:
         * - Dados obrigatórios ausentes ou inválidos
         *
         * @note Este método não cria a sessão diretamente; apenas retorna os dados
         * necessários para que o serviço superior gerencie a sessão do usuário.
         */
        
        public function handle($data){

            // Extrai os dados necessários do array de entrada
            $email = $data["email"];
            $password = $data["password"];
            $rememberChecked = $data["remember"] ?? null;
            $rememberCookie = isset($_COOKIE["rememberCookie"]);

            // Verifica se os dados estão presentes e são válidos
            valueVerification($email);
            valueVerification($password);
            valueVerification($rememberChecked);
            valueVerification($rememberCookie);

            $mysqli = Database::connect(); 

            // Seleciona o usuário com o email fornecido para verificar se ele existe
            $stmt = Database::query(
                $mysqli,
                "SELECT id FROM user WHERE email = ?",
                false,
                "s",
                [$email]
            ); 
            
            // Se nenhum usuário for encontrado com o email fornecido, retorna um erro de credenciais inválidas
            if ($stmt->num_rows === 0) {
                $stmt->close();
                $mysqli->close();

                return [
                    "success" => false,
                    "message" => "Usuário ou senha incorretos!",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            // Pega a senha do banco para o usuário encontrado e verifica se a senha fornecida corresponde à senha armazenada usando password_verify
            $row = Database::query(
                $mysqli,
                "SELECT senha FROM user WHERE email = ?",
                true,
                "s",
                [$email],
                true
            );

            $senhaBanco = $row["senha"];
            valueVerification($senhaBanco);
            
            // Verifica se a senha fornecida corresponde à senha armazenada usando password_verify. Se corresponder, o login é bem-sucedido e retorna um array de sucesso. Caso contrário, retorna um array de erro de credenciais inválidas.
            if (password_verify($password, $senhaBanco)) {
                
                if(UserSessions::isLoggedIn()){
                    UserSessions::revokeSession(); 
                }

                $user_config = UserRepository::getUserConfig($mysqli, null, $email);
                $mysqli->close();

                // Se o usuário está com o 2fa ligado ele não loga e é direcionado a página de 2fa
                if(!(bool) $user_config["two_factor_enabled"]){
                    return [
                        "success" => true,
                        "message" => "Login efetuado com sucesso!",
                        "action" => "REDIRECT",
                        "data" => [
                            "login" => true,
                            "email" => $email,
                            "rememberChecked" => $rememberChecked,
                            "rememberCookie" => $rememberCookie,
                            "rememberCookieToken" => $_COOKIE["rememberCookie"] ?? null,
                            "changeLocation" => true
                        ]
                    ];
                }

                else {
                    return [
                        "success" => true,
                        "message" => "2fa está ativo para esta conta!",
                        "2fa" => $user_config["two_factor_enabled"],
                        "action" => "REDIRECT",
                        "data" => null
                    ];                   
                }

            } 
            
            $mysqli->close();

            return [
                "success" => false,
                "message" => "Usuário ou senha incorretos",
                "action" => "SHOW_ERROR",
                "data" => null
            ];
        }

    }