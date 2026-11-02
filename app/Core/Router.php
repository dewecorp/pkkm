<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, $handler, array $roles = []) {
        $this->addRoute('GET', $path, $handler, $roles);
    }

    public function post(string $path, $handler, array $roles = []) {
        $this->addRoute('POST', $path, $handler, $roles);
    }

    private function addRoute(string $method, string $path, $handler, array $roles = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'roles' => $roles
        ];
    }

    public function dispatch(string $method, string $uri) {
        // Strip query string and base subfolder
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Handle Laragon subdirectory /pkkm/ or /pkkm/public/
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            if (strpos($path, $scriptName) === 0) {
                $path = substr($path, strlen($scriptName));
            }
        }
        $path = '/' . trim($path, '/');
        if ($path === '//') $path = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                // Role verification
                if (!empty($route['roles'])) {
                    if (!Auth::check()) {
                        header('Location: ' . base_url('login'));
                        exit;
                    }
                    if (!Auth::hasAnyRole($route['roles'])) {
                        http_response_code(403);
                        echo "403 Forbidden - Anda tidak memiliki akses ke halaman ini.";
                        exit;
                    }
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->invokeHandler($route['handler'], $params);
            }
        }

        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }

    private function invokeHandler($handler, array $params) {
        $indexedParams = array_values($params);

        if (is_callable($handler)) {
            return call_user_func_array($handler, $indexedParams);
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return call_user_func_array([$controller, $method], $indexedParams);
        }
    }
}
