<?php

// Enrutador. Su trabajo es mirar la URL que esrcibe el usuario, compararla con las rutas definidas en el código y llamar al controlador y acción correspondientes.
// Si no encuentra una ruta que coincida, muestra una página de error 404.

namespace OfficeHub\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                );

                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass($request);
                $controller->$action($params);
                return;
            }
        }

        http_response_code(404);
        require BASE_PATH . '/src/Views/errors/404.php';
    }
}
