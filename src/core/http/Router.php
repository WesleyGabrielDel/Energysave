<?php 
class Router
{
    private array $routes = [];

    public function get($path, $action){   
        // Chama o método add para registrar a rota GET com o caminho e a ação
        $this->add('GET', $path, $action);
    }

    public function post($path, $action){
        // Chama o método add para registrar a rota POST com o caminho e a ação
        $this->add('POST', $path, $action);
    }

    private function add($method, $path, $action){
        // Adiciona uma nova rota ao array de rotas
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action
        ];
    }
 
    public function dispatch($method, $uri){
        // Limpa a URI
        $uri = $this->cleanUri($uri);

        foreach ($this->routes as $route) {

            // Se o método da rota não corresponder ao método da requisição
            if ($route['method'] !== $method) {
                continue;
            }

            // Converte o caminho da rota para o formato de expressão regular
            $pattern = $this->convertRouteToRegex($route['path']);

            // Se a URI corresponder ao padrão da rota
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $this->runAction($route['action'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function runAction($action, $params){
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        if (is_string($action)) {
            [$controller, $method] = explode('@', $action);
            return (new $controller)->$method(...$params);
        }
    }

    private function convertRouteToRegex($path){
        return "#^" . preg_replace('#\{[^}]+\}#', '([^/]+)', $path) . "$#";
    }

    private function cleanUri($uri){
        return parse_url($uri, PHP_URL_PATH);
    }
}
