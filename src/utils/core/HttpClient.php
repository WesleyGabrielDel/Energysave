<?php 

    class HttpClient {
        
        /**
         * Faz uma requisição HTTP GET para uma API externa usando cURL.
         * Configura SSL, timeouts e verifica erros de conexão, HTTP e JSON.
         * Retorna os dados decodificados da resposta ou sai com erro se houver falha.
         *
         * @param string $url URL da API externa (ex.: https://api.exemplo.com/endpoint)
         * @return array Dados decodificados do JSON da resposta
         */

        public static function get($url){
            try{
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

                $response = curl_exec($ch);

                if($response === false){
                    curl_close($ch);
                    http_response_code(502);
                    echo json_encode(["error" => "Erro na conexão com o servidor!"]);
                    exit;
                }

                if(curl_errno($ch)){
                    curl_close($ch);
                    http_response_code(502);
                    echo json_encode(["error" => "Erro na conexão com o servidor!"]);
                    exit;
                }

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                curl_close($ch);

                if($httpCode >= 400){
                    http_response_code(502);
                    echo json_encode(["error" => "Falha na API externa"]);
                    exit;
                }

                $data = json_decode($response, true);

                if(json_last_error() !== JSON_ERROR_NONE){
                    http_response_code(422);
                    echo json_encode(["error" => "Resposta inválida da API externa!"]);
                    exit;            
                }

                return $data;
            }

            catch(Throwable $e){
                errorReport(400, "Não foi possível realizar a requisição para a API. Erro: $e");
            }
        }

        public static function post($url, $data = null, $returnResponse = false) {
            return self::request("POST", $url, $data, $returnResponse);
        }

        /**
         * Executa uma requisição HTTP genérica usando cURL (GET, POST, PUT, DELETE, etc.).
         * Suporta dados JSON e headers customizados.
         * Se $returnResponse=true, retorna a resposta; senão, apenas executa.
         * Trata erros com try-catch e sai com errorReport.
         *
         * @param string $method Método HTTP (ex.: "GET", "POST")
         * @param string $url URL da API
         * @param array|null $data Dados a enviar (pode incluir "headers" para cabeçalhos)
         * @param bool $returnResponse Se true, retorna a resposta; senão, void
         * @return mixed Resposta da API se $returnResponse=true
         */

        public static function request($method, $url, $data = null, $returnResponse = false) {
            try{
                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

                $headers = [];
                if (!empty($data)) {
                    if (isset($data["headers"])) {
                        $headers = $data["headers"];
                        unset($data["headers"]);
                    }

                    if (!empty($data)) {
                        $jsonData = json_encode($data);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                        $headers[] = "Content-Type: application/json";
                        $headers[] = "Content-Length: " . strlen($jsonData);
                    }
                }

                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }

                if ($returnResponse) {
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                }

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    errorReport(502, "Erro na requisição");
                }

                curl_close($ch);

                if ($returnResponse) {
                    return $result;
                }
            }

            catch(Throwable $e){
                errorReport(400, "Não foi possível chamar ou fazer a requisição para a API. Url: $url. Erro: $e");
            }
        }
    }