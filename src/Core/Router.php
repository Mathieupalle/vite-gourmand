<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    // Ajouter une route
    public function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    // Lancer le routeur
    public function run(): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    $handler();
                } elseif (is_array($handler) && class_exists($handler[0]) && method_exists($handler[0], $handler[1])) {
                    $controller = new $handler[0]();
                    $controller->{$handler[1]}();
                }
                return;
            }
        }

        // Route non trouvée
        http_response_code(404);
        echo "Page introuvable : {$requestUri}";
    }
}