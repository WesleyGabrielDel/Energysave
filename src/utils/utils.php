<?php 

    /**
     * Verifica se uma variável é válida (não nula, não vazia e, se array, não vazio).
     * Opcionalmente, valida o tipo da variável (string, int, float, bool, array, object, resource, callable, iterable).
     * Se inválida, retorna erro HTTP 422 (dados incorretos) ou 415 (tipo incorreto) e sai.
     *
     * @param mixed $var Valor a ser verificado
     * @param string|null $type Tipo esperado (opcional). Valores: "string", "int", "float", "bool", "array", "object", "resource", "callable", "iterable"
     * @param bool $required Se true, verifica se o valor é obrigatório (não vazio);
     * @return void
     */
    
    function valueVerification($var, $type = null, $required = true){
        if(is_array($var)){
            foreach ($var as $i => $value) {
                if($value === null){
                    errorReport(402, "Dados Incorretos (Função ValueVerification)");
                }
            }
        }
        else {
            if($var === null){
                errorReport(402, "Dados Incorretos (Função ValueVerification)");
            }

        }
    }
    
    /**
     * Valida se campos de um array existem. Se não existirem, ele dá erro
     *
     * @param array $array Array a ser verificado
     * @param iterable|object $requiredFields Campos a serem verificados
     * @return void
     */

    function verifyArrayFields($array, $requiredFields){

        if(!is_array($array)){
            errorReport(400, "Estrutura inválida");
        }

        foreach($requiredFields as $field){
            if(!isset($array[$field])){
                errorReport(422, "Campo obrigatório ausente");
            }
        }
    }

    /**
     * Verifica se o usuário está logado por meio do cookie de sessão.
     * Se não logado ou token expirado, redireciona para home/login.
     * Remove tokens expirados do banco.
     *
     * @param string|false $page Página para redirecionar se não autenticado (ex.: "../auth-page/index.php"). Se false, usa padrão.
     * @return void
     */

    function requireAuth($page = false){
        if(!$page){
            $page = "http://localhost/EnergySaveProject/auth";
        }

        try {
            // Conectando ao banco de dados
            $mysqli = Database::connect();

            // Pegando o valor do cookie do usuário
            if(isset($_COOKIE["rememberCookie"])){
                $authToken = $_COOKIE["rememberCookie"];
            }
            else {
                header("Location: " . $page);
                exit;
            }

            $tokenData = Database::query($mysqli, "SELECT token, exp FROM remember_tokens WHERE token = ?", true, "s", [$authToken]);

            // ? Verificando se o token existe no banco
            if($tokenData !== null){
                // Se ele existe no banco
                if($tokenData["exp"] < time()){
                    Database::query($mysqli, "DELETE FROM remember_tokens WHERE token = ?", false, "s", [$tokenData["token"]]);
                    setcookie("rememberCookie", "", [
                        "expires" => 0,
                        "path" => "/",
                        "secure" => true,
                        "httponly" => true,
                        "samesite" => "Strict"
                    ]);

                    header("Location: " . $page);
                    exit;
                }

            }
            else {
                // Se ele não existe no banco
                header("Location: " . $page);
                exit;
            }        

            $mysqli->close();
        } 
        
        catch (Throwable $e) {
            errorReport(400, "Não foi possível verificar a autenticação do usuário. Erro: $e");
            header("Location: http://localhost/EnergySaveProject/auth");
            exit;
        }
    }

    /**
     * Gera e exibe um erro formatado com suporte a debug.
     * Define o código HTTP de resposta, cria um JSON de erro padronizado e sai da execução.
     * Se DEBUG estiver ativado, inclui a linha do arquivo onde o erro ocorreu.
     *
     * @param int $errorCode Código HTTP do erro (ex.: 400, 401, 500)
     * @param string $errorMessage Mensagem descritiva do erro
     * @return bool|null
     */

    function errorReport(int $errorCode, string $errorMessage) {
        $linha = null;

        if (defined('DEBUG') && DEBUG === true) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $linha = $trace[0]['line'] ?? null;
        }

        http_response_code($errorCode);

        $response = [
            "success" => false,
            "message" => $errorMessage,
            "action" => "SHOW_ERROR",
            "data" => null
        ];

        if ($linha !== null) {
            $response["debug"] = [
                "lineInMainArchive" => $linha
            ];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Retorna uma mensagem padronizada de erro. 
     * Menos agressivo que errorReport, mandando uma mensagem padronizada.
     *
     * @param string $message Mensagem do erro
     * @param string $action  Ação do front-end ao receber o erro
     * @param array $data     Dados a serem retornados
     * @return void
     */

    function returnErrorStatus($message, $action = "SHOW_ERROR", $data = null){
        echo json_encode(
            [
                "success" => false, 
                "message" => $message, 
                "action" => $action, 
                "data" => $data
            ]
        );
        exit;
    }

    /**
     * Obtém e decodifica JSON enviado no corpo da requisição (via php://input).
     * Usada para receber dados de requisições fetch/API.
     * Trata erros de decodificação JSON e sai com erro 409 se falhar.
     *
     * @return array Dados recebidos decodificados ou array vazio se erro
     */

    function getJson(){
        try {
            $data = json_decode(file_get_contents("php://input"), true); // Pega o que foi enviado via fetch

            if(json_last_error() !== JSON_ERROR_NONE){ // Caso der erro
                http_response_code(422);
                echo json_encode(["error" => "Erro ao resgatar as informações!", "message" => json_last_error_msg()]);
                exit;            
            }

            return $data ?? [];
        }
        catch(Throwable $e){
            errorReport(409, "Não foi possível resgatar as informações do JSON. Erro: $e");
        }
    }    

