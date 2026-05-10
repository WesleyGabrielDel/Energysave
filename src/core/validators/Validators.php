<?php 
    class Validators {

        public static function validateName(string $name){
            if($name === null || !is_string($name) || empty($name)){
                returnErrorStatus("Nome inválido inserido!");
            }

            if(mb_strlen($name, "UTF-8") > 70) {
                returnErrorStatus("O nome deve ter menos que 70 caracteres!");
            }   

            return true;
        }

        public static function validateBirthData(string $data) {

            if (empty($data)) {
                return true;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                returnErrorStatus("Formato de data inválido. Use AAAA-MM-DD.");
            }

            [$ano, $mes, $dia] = explode('-', $data);

            if ($ano < 1900 || $ano > date('Y')) {
                returnErrorStatus("Ano inválido.");
            }

            if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
                returnErrorStatus("Data inexistente.");
            }

            $d = new DateTime($data);
            $hoje = new DateTime();

            if ($d > $hoje) {
                returnErrorStatus("A data de nascimento não pode ser no futuro.");
            }

            $diferenca = $hoje->diff($d);
            if ($diferenca->y > 120) {
                returnErrorStatus("A idade inserida ({$diferenca->y} anos) é inválida.");
            }

            return true; 
        }

        public static function validatePhone(string $phone) {

            if (empty($phone)) {
                return true;
            }

            $phone = preg_replace('/\D/', '', $phone);

            if ((strlen($phone) === 12 || strlen($phone) === 13) && substr($phone, 0, 2) === '55') {
                $phone = substr($phone, 2);
            }

            if (strlen($phone) < 10 || strlen($phone) > 11) {
                returnErrorStatus("Telefone inválido.");
            }

            $ddd = substr($phone, 0, 2);
            $numero = substr($phone, 2);

            if ($ddd < 11 || $ddd > 99) {
                returnErrorStatus("DDD inválido.");
            }

            if (strlen($numero) === 9) {
                if ($numero[0] != '9') {
                    returnErrorStatus("Celular inválido.");
                }
            } elseif (strlen($numero) === 8) {
                if (in_array($numero[0], ['0', '1'])) {
                    returnErrorStatus("Telefone fixo inválido.");
                }
            } else {
                returnErrorStatus("Telefone inválido.");
            }

            return true;
        }
        
    }