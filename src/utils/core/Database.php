<?php 

    class Database {
        
        /**
         * Conecta ao banco de dados MySQL usando mysqli.
         * Lança exceção se houver erro de conexão e sai com errorReport.
         *
         * @param string $host Host do servidor MySQL
         * @param string $usuario Usuário do banco
         * @param string $senha Senha do banco
         * @param string $database Nome do banco de dados
         * @return mysqli Objeto de conexão mysqli
         */

        public static function connect($host = null, $usuario = null, $senha = null, $database = null){
            if($host === null) $host = DBHOST;
            if($usuario === null) $usuario = DBUSER;
            if($senha === null) $senha = DBPASSWORD;
            if($database === null) $database = DBNAME;

            try{
                $mysqli = new mysqli($host, $usuario, $senha, $database);

                if ($mysqli->connect_error) {
                    throw new Exception("Erro ao conectar ao banco");
                }
                return $mysqli;
            }

            catch(Throwable $e){
                errorReport(400, "Não foi possível realizar a conexão com o banco de dados. Erro: $e");
            }
        }

        /**
         * Executa uma query SQL preparada no banco de dados.
         * Usa bind_param para segurança contra SQL injection.
         * Se $return=true, retorna o resultado como array; senão, retorna o stmt.
         * Trata erros com try-catch e sai com errorReport.
         *
         * @param mysqli $mysqli Conexão ativa com o banco
         * @param string $sql Query SQL a executar (com placeholders ?)
         * @param bool $return Se true, retorna fetch_assoc; senão, stmt
         * @param string|null $types Tipos dos parâmetros (ex.: "s" para string, "i" para int)
         * @param array|null $params Valores dos parâmetros
         * @param bool $mustExist Se true, verifica se o resultado existe e retorna erro 400 se não existir
         * @return mysqli_stmt|array Stmt ou array do resultado
         */

        public static function query($mysqli, $sql, $return = false, $types = null, $params = null, $mustExist = false) {
            try {

                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    errorReport(500, "Erro ao preparar query");
                }

                if ($types !== null && $params !== null) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();

                if (!$return) {
                    $stmt->store_result();

                    if ($mustExist && $stmt->num_rows === 0) {
                        errorReport(400, "Registro não encontrado!");
                    }
                    return $stmt;
                } 

                else {
                    $result = $stmt->get_result();
                    $data = $result->fetch_assoc();

                    if ($mustExist && !$data) {
                        errorReport(400, "Registro não encontrado!");
                    }
                    return $data;
                }

            }
            
            catch(Exception $e){
                errorReport(400, "Não foi possível realizar a execução da query. Erro: $e");
            }
        }

    }