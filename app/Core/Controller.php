<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Controller.php
 * DESCRIÇÃO: Controlador Base com métodos utilitários de renderização e redirecionamento
 * =====================================================================
 */

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza uma view PHP dentro do layout principal ou diretamente
     *
     * @param string $view Caminho relativo da view (ex: 'equipamentos/index')
     * @param array $data Dados a serem extraídos para a view
     * @param string|null $layout Nome do layout (padrão 'main', ou null para nenhum)
     */
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        // Garante que a sessão está ativa para flashes e checagem de usuário
        Auth::startSession();

        // Variáveis globais úteis na view
        $data['currentUser'] = Auth::user();
        $data['flashSuccess'] = $_SESSION['flash_success'] ?? null;
        $data['flashError'] = $_SESSION['flash_error'] ?? null;
        $data['flashWarning'] = $_SESSION['flash_warning'] ?? null;

        // Limpa mensagens flash após consumo
        unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_warning']);

        // Extrai variáveis para o escopo local da view
        extract($data);

        $viewFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Erro: View '{$view}' não foi encontrada.";
            exit;
        }

        // Se layout for especificado, captura o conteúdo da view em buffer
        if ($layout !== null) {
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            $layoutFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            require $viewFile;
        }
        exit;
    }

    /**
     * Redireciona para outra URL da aplicação
     *
     * @param string $path Caminho relativo (ex: '/equipamentos')
     */
    protected function redirect(string $path): void
    {
        $base = '/sce/public';
        $target = (strpos($path, 'http') === 0) ? $path : $base . '/' . ltrim($path, '/');
        header("Location: {$target}");
        exit;
    }

    /**
     * Define uma mensagem flash de sucesso
     *
     * @param string $message
     */
    protected function setFlashSuccess(string $message): void
    {
        Auth::startSession();
        $_SESSION['flash_success'] = $message;
    }

    /**
     * Define uma mensagem flash de erro
     *
     * @param string $message
     */
    protected function setFlashError(string $message): void
    {
        Auth::startSession();
        $_SESSION['flash_error'] = $message;
    }

    /**
     * Define uma mensagem flash de aviso
     *
     * @param string $message
     */
    protected function setFlashWarning(string $message): void
    {
        Auth::startSession();
        $_SESSION['flash_warning'] = $message;
    }

    /**
     * Retorna uma resposta JSON padronizada
     *
     * @param array $data
     * @param int $statusCode
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Valida requisição POST e confere token CSRF
     *
     * @return bool
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!Csrf::validate($token)) {
            $this->setFlashError('Sessão expirada ou token de segurança inválido. Tente novamente.');
            return false;
        }
        return true;
    }
}
