<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Router.php
 * DESCRIÇÃO: Roteador HTTP simplificado e robusto para arquitetura MVC
 * =====================================================================
 */

namespace App\Core;

class Router
{
    /**
     * Mapeamento de rotas registradas organizadas por método HTTP
     * @var array<string, array<string, array{controller: string, action: string, middleware: array}>>
     */
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Registra uma rota GET
     *
     * @param string $path Caminho da rota (ex: '/equipamentos')
     * @param string $controller Nome da classe do controlador
     * @param string $action Nome do método
     * @param array $middleware Lista de middlewares (ex: ['auth'])
     */
    public function get(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $controller, $action, $middleware);
    }

    /**
     * Registra uma rota POST
     *
     * @param string $path Caminho da rota
     * @param string $controller Nome da classe do controlador
     * @param string $action Nome do método
     * @param array $middleware Lista de middlewares
     */
    public function post(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $controller, $action, $middleware);
    }

    /**
     * Armazena a definição da rota internamente
     */
    private function addRoute(string $method, string $path, string $controller, string $action, array $middleware): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    /**
     * Processa a URI recebida na requisição atual e despacha para o controlador
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove a query string (?pagina=2 etc)
        $uriPath = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Normaliza barras invertidas caso venham do Windows
        $uriPath = str_replace('\\', '/', $uriPath);

        // Remove o prefixo "/sce/public" ou "/sce" ou "/index.php" do início da URL
        $prefixes = ['/sce/public/index.php', '/sce/public', '/sce/index.php', '/sce', '/index.php'];
        foreach ($prefixes as $prefix) {
            if (strpos($uriPath, $prefix) === 0) {
                $uriPath = substr($uriPath, strlen($prefix));
                break;
            }
        }

        $uriPath = '/' . trim($uriPath, '/');
        if ($uriPath === '') {
            $uriPath = '/';
        }

        // Busca rota exata
        if (isset($this->routes[$method][$uriPath])) {
            $route = $this->routes[$method][$uriPath];
            $this->executeRoute($route);
            return;
        }

        // Rota não encontrada (404)
        http_response_code(404);
        $this->render404($uriPath);
    }

    /**
     * Executa os middlewares e aciona a ação do controlador
     *
     * @param array $route
     */
    private function executeRoute(array $route): void
    {
        // Execução de middlewares
        if (!empty($route['middleware'])) {
            foreach ($route['middleware'] as $mw) {
                if ($mw === 'auth') {
                    Auth::requireLogin();
                }
            }
        }

        $controllerClass = "App\\Controllers\\" . $route['controller'];
        $action = $route['action'];

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Erro: Controlador '{$controllerClass}' não foi encontrado.";
            return;
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $action)) {
            http_response_code(500);
            echo "Erro: Método '{$action}' não existe no controlador '{$controllerClass}'.";
            return;
        }

        // Executa a ação
        $controllerInstance->$action();
    }

    /**
     * Renderiza página 404 amigável
     *
     * @param string $path
     */
    private function render404(string $path): void
    {
        $viewFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . '404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<h1>404 — Página Não Encontrada</h1>";
            echo "<p>A rota requisitada [<code>" . htmlspecialchars($path) . "</code>] não existe no sistema SCE.</p>";
            echo "<p><a href='/sce/public/'>Voltar ao Início</a></p>";
        }
    }
}
