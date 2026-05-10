<?php 

    class Security {

        /**
         * Valida um JWT (JSON Web Token), garantindo que seja utilizado somente uma vez por requisição.
         * Verifica estrutura (3 partes), assinatura HMAC-SHA256, expiração e JTI (único uso).
         * Sai com erro 401 se inválido. Nota: Recomenda implementar blacklist para JTI no banco.
         *
         * @param string $jwt Token JWT a ser validado
         * @param string $excludeVerification Permite excluir verificações (ex.: "ex-exp" para ignorar expiração)
         * @return void
         */

        public static function validateJwt($jwt, $excludeVerification = ''){

            $parts = explode(".", $jwt); 
            if(count($parts) !== 3){
                http_response_code(401);
                echo json_encode(["error" => "Quantidade de partes inválida!"]);
                exit;              
            }

            $secret = API_SECRET;

            $payload = $parts[1]; 
            $payloadDecoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true); 

            // Verificando se o token já expirou
            if($excludeVerification !== "ex-exp" && isset($payloadDecoded["exp"]) && time() > $payloadDecoded["exp"]){ // Se o tempo de agr foi maior que o tempo que está no exp
                http_response_code(401);
                echo json_encode(["error" => "Token expirado!"]);
                exit;                       
            }

            $header = $parts[0];
            $headerDecoded = json_decode(base64_decode(strtr($header, '-_', '+/')), true); // Parte do header decodificado

            $base = $parts[0] . "." . $parts[1]; // Junta o Header e o Payload

            $signatureHash = hash_hmac("sha256", $base, $secret, true); // Retorna um hash baseado na chave secreta e nos dados. Garantindo que não seja alterado
            $signature = rtrim(strtr(base64_encode($signatureHash), '+/', '-_'), '='); // Transforma no formato JWT

            if(!hash_equals($signature, $parts[2])){
                http_response_code(401);
                echo json_encode(["error" => "Token Inválido!"]);
                exit;           
            }

            // Verificando o jti para que o mesmo token não seja utilizado de novo
            $jti = $payloadDecoded["jti"];
            
        }

        /**
         * Gera um JWT (JSON Web Token), setando seu tempo de expiração
         *
         * @param int $timeExp Tempo de expiração do token
         * @return string Token
         */     

        public static function generateJwt($timeExp){
            valueVerification($timeExp, "int");

            $secret = API_SECRET; 
            $header = [
                "alg" => "HS256",
                "typ" => "JWT"
            ];

            $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');

            $payload = [
                "sub" => 123, 
                "iat" => time(), 
                "exp" => time() + $timeExp, 
                "jti" => bin2hex(random_bytes(16))
            ];

            $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
            $signatureBase = $headerEncoded . "." . $payloadEncoded;

            $signature = hash_hmac("sha256", $signatureBase, $secret, true); 
            $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

            $jwt = $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded; 
            return $jwt;
        }

        /**
         * Rate limit com proteção contra flood simultâneo.
         *
         * @param int $maxRequests Número máximo de requisições
         * @param int $windowSeconds Janela de tempo (segundos)
         * @param int $banSeconds Tempo de banimento (segundos)
         * @return void
         */

        public static function rateLimit($maxRequests, $windowSeconds, $banSeconds) {
            
        }
    }