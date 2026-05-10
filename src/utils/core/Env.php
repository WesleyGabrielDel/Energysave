<?php 

    class Env {
        /**
         * Carrega variáveis do arquivo .env.
         *
         * Caso $fieldsToGet seja informado, apenas os campos especificados serão carregados.
         * Caso contrário, todas as variáveis do .env serão retornadas.
         *
         * Os valores podem ser utilizados para definição de constantes ou configuração da aplicação.
         *
         * @param array|null $fieldsToGet Lista de chaves do .env a serem carregadas (opcional)
         * @return array Retorna um array associativo no formato ['CHAVE' => valor]
         */

        public static function get($fieldsToGet = null) {
            $root = $_SERVER['DOCUMENT_ROOT'] . '/EnergySaveProject';
            $envPath = $root . '/.env';

            if (!file_exists($envPath)) {
                throw new Exception('.env não encontrado');
            }

            $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

            if ($fieldsToGet === null) {
                $fields = [
                    "API_SECRET",
                    "API_KEY",
                    "DEBUG",
                    "ORIGIN",
                    "DBUSER",
                    "DBPASSWORD",
                    "DBNAME",
                    "DBHOST"
                ];
            } else {
                $fields = is_array($fieldsToGet) ? $fieldsToGet : [$fieldsToGet];
            }

            $result = [];

            foreach ($fields as $field) {
                $value = $env[$field] ?? null;

                if ($value === 'true') $value = true;
                if ($value === 'false') $value = false;

                $result[$field] = $value;
            }

            return $result;
        }

    }