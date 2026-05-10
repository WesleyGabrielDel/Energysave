<?php 

    class AuthValidator {

        /**
         * Valida as credenciais de login do usuário.
         *
         * Verifica se o email e a senha foram fornecidos e se o email possui um formato válido.
         * Caso algum dado seja inválido, uma função de erro é chamada interrompendo a execução.
         *
         * @param array $data Array associativo contendo as credenciais do usuário. Espera-se as chaves: email (string), password (string)
         * @return void
         */

        public static function validateCredentials(array $data){
            $email = $data["email"] ?? null;
            valueVerification($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                errorReport(400, "Email inválido");
            }

            $password = $data["password"] ?? null;
            valueVerification($password);
        }

        /**
         * Valida o nome do usuário.
         *
         * Verifica se o nome é uma string válida, não nula e se possui no máximo 70 caracteres.
         * Caso o valor seja inválido, uma função de erro é chamada.
         *
         * @param string $name Nome do usuário a ser validado.
         * @return bool Retorna true se o nome for válido.
         */

        public static function validateName(string $name){
            if($name === null || !is_string($name)){
                returnErrorStatus("Nome inválido inserido!");
            }

            if(mb_strlen($name, "UTF-8") > 70) {
                returnErrorStatus("O nome deve ter menos que 70 caracteres!");
            }   

            return true;
        }

    }