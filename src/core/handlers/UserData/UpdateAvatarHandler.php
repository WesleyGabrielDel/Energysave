<?php

    class UpdateAvatarHandler {
        
        /**
         * Processa o upload e atualização da imagem de avatar do usuário.
         *
         * Recebe uma imagem em formato Base64, valida sua integridade, tamanho e formato,
         * realiza a conversão para binário, salva o arquivo no servidor e atualiza o caminho
         * da imagem no banco de dados associado ao usuário autenticado via token.
         *
         * Fluxo:
         * 1. Valida o token de autenticação (accountToken)
         * 2. Extrai a string Base64 da imagem do array de entrada
         * 3. Verifica se o formato Base64 é válido (contém prefixo com ",")
         * 4. Decodifica a imagem Base64 para binário
         * 5. Valida se a conversão foi bem-sucedida
         * 6. Verifica se o tamanho da imagem não excede o limite (5MB)
         * 7. Gera um nome único para o arquivo
         * 8. Salva a imagem no diretório de avatares
         * 9. Busca o usuário associado ao token
         * 10. Atualiza o caminho da imagem no banco de dados
         *
         * @param mysqli $mysqli Conexão ativa com o banco de dados.
         * @param array $data [src => string] Dados da requisição contendo a imagem em Base64.
         * - src: string Base64 no formato "data:image/png;base64,..."
         *
         * @param string $accountToken Token de autenticação utilizado para identificar o usuário.
         *
         * @return array Retorna um array contendo:
         * - message (string): mensagem de sucesso ou erro
         * - typeMessage (string, opcional): tipo da mensagem (ex: success)
         * OU (em alguns fluxos):
         * - success (bool)
         * - action (string)
         * - data (array|null)
         *
         * @throws void Interrompe a execução através de errorReport() em caso de:
         * - Token inválido ou não associado a usuário
         *
         * @note O método assume que a imagem enviada é do tipo PNG e salva o arquivo com essa extensão.
         * @note O caminho salvo no banco é uma URL absoluta baseada no ambiente local.
         */

        public function handle($mysqli, $data, $accountToken){
            valueVerification($accountToken, "string");

            $base64 = $data["src"] ?? null;

            if (!$base64) {
                return [
                    "success" => false,
                    "message" => "Sem imagem",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            valueVerification($base64, "string");

            if (!str_contains($base64, ",")) {
                return [
                    "success" => false,
                    "message" => "Formato inválido",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $base64 = explode(",", $base64)[1];
            $imageData = base64_decode($base64, true);

            if ($imageData === false) {
                return [
                    "success" => false,
                    "message" => "Imagem inválida",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            if (strlen($imageData) > 5 * 1024 * 1024) {
                return [
                    "success" => false,
                    "message" => "Imagem muito grande",
                    "action" => "SHOW_ERROR",
                    "data" => null
                ];
            }

            $fileName = uniqid() . ".png";

            $filePath = "../../public/images/avatars/" . $fileName;
            file_put_contents($filePath, $imageData);

            $dbPath = BASE_URL . "public/images/avatars/" . $fileName;

            $row = Database::query(
                $mysqli, 
                "SELECT user_id FROM remember_tokens WHERE token = ?", 
                true, 
                "s", 
                [$accountToken], 
                true
            );

            if ($row !== null) {
                Database::query(
                    $mysqli, 
                    "UPDATE user SET profile_picture = ? WHERE id = ?", 
                    false, 
                    "si", 
                    [$dbPath, $row["user_id"]]
                );

                return [
                    "message" => "Avatar atualizado!",
                    "typeMessage" => "success"
                ];
            } 
            
            else {
                errorReport(400, "Sessão inválida!");
            }

            return [
                "success" => true,
                "message" => "Avatar atualizado!",
                "action" => "SHOW_SUCCESS",
                "data" => null
            ];
        }

    }