<?php 

    class GetDeviceInfoHandler {
        
        /**
         * Coleta e formata informações do dispositivo do usuário a partir da requisição HTTP.
         *
         * Extrai dados do ambiente (IP e User-Agent), identifica sistema operacional,
         * navegador (origem do login) e tipo de dispositivo, e retorna essas informações
         * em formato JSON.
         *
         * Fluxo:
         * 1. Obtém o IP do usuário a partir dos headers disponíveis (Cloudflare, proxy ou remoto)
         * 2. Trata possíveis múltiplos IPs (X-Forwarded-For)
         * 3. Obtém o User-Agent da requisição
         * 4. Identifica o sistema operacional com base em padrões conhecidos
         * 5. Identifica o tipo de dispositivo (Desktop, Mobile ou Tablet)
         * 6. Identifica o navegador/origem do login
         * 7. Monta a estrutura final com os dados coletados
         * 8. Retorna as informações em formato JSON
         *
         * @return string|null JSON contendo:
         * - ip (string|null): IP do usuário
         * - sistema_operacional (string|null): Sistema operacional detectado
         * - origem_login (string|null): Navegador utilizado (Chrome, Firefox, etc)
         * - tipo_dispositivo (string|null): Tipo de dispositivo (Desktop, Mobile, Tablet)
         * - localizacao (null): Campo reservado para futura implementação
         *
         * @note A detecção é baseada em padrões de User-Agent e pode não ser 100% precisa.
         * @note O campo "localizacao" está definido como null e pode ser implementado futuramente com serviços externos.
         */

        public function handle(): ?string {

            // Pega o IP do usuário
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR']
                ?? null;

            if ($ip && strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Inicializando as variáveis
            $sistema_operacional = null;
            $origem_login = null;
            $tipo_dispositivo = null;
            
            // Formatando e pegando o sistema operacional
            if ($userAgent) {

                if (preg_match('/windows nt/i', $userAgent)) $sistema_operacional = 'Windows';
                elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $sistema_operacional = 'Mac';
                elseif (preg_match('/linux/i', $userAgent) && !preg_match('/android/i', $userAgent)) $sistema_operacional = 'Linux';
                elseif (preg_match('/android/i', $userAgent)) $sistema_operacional = 'Android';
                elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $sistema_operacional = 'iOS';

                if (preg_match('/ipad|tablet/i', $userAgent)) $tipo_dispositivo = 'Tablet';
                elseif (preg_match('/mobile/i', $userAgent) && preg_match('/android|iphone/i', $userAgent)) $tipo_dispositivo = 'Mobile';
                else $tipo_dispositivo = 'Desktop';

                if (preg_match('/edg/i', $userAgent)) $origem_login = 'Edge';
                elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) $origem_login = 'Chrome';
                elseif (preg_match('/firefox/i', $userAgent)) $origem_login = 'Firefox';
                elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) $origem_login = 'Safari';
            }

            $localizacao = null;

            return json_encode([
                "ip" => $ip,
                "sistema_operacional" => $sistema_operacional,
                "origem_login" => $origem_login,
                "tipo_dispositivo" => $tipo_dispositivo,
                "localizacao" => $localizacao,
            ]);
        }

    }