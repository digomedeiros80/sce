<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Auth.php
 * DESCRIÇÃO: Gestão de Autenticação Administrativa e Controle de Sessão
 * =====================================================================
 */

namespace App\Core;

class Auth
{
    /**
     * Inicia a sessão configurando parâmetros seguros (HttpOnly, SameSite, Lifetime)
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Em modo CLI (testes no terminal) os headers de cookie não se aplicam
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
                $config = file_exists($configPath) ? require $configPath : [];
                $sessionConfig = $config['session'] ?? [];

                $lifetime = $sessionConfig['lifetime'] ?? 7200;
                $path     = $sessionConfig['path'] ?? '/';
                $secure   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($sessionConfig['secure'] ?? false);
                $httponly = $sessionConfig['httponly'] ?? true;
                $samesite = $sessionConfig['samesite'] ?? 'Lax';

                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path'     => $path,
                    'domain'   => '',
                    'secure'   => $secure,
                    'httponly' => $httponly,
                    'samesite' => $samesite,
                ]);

                if (!empty($sessionConfig['name'])) {
                    session_name($sessionConfig['name']);
                }
            }

            @session_start();
        }
    }

    /**
     * Registra o usuário autenticado na sessão com regeneração de identificador (prevenção contra Session Fixation)
     *
     * @param array $admin Dados do administrador autenticado
     */
    public static function login(array $admin): void
    {
        self::startSession();

        // Regenera o ID de sessão para prevenir sequestro/fixação de sessão
        session_regenerate_id(true);

        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id']     = (int) $admin['id'];
        $_SESSION['admin_nome']   = $admin['nome'];
        $_SESSION['admin_email']  = $admin['email'];
        $_SESSION['last_activity']= time();
    }

    /**
     * Encerra a sessão administrativa de forma completa e segura
     */
    public static function logout(): void
    {
        self::startSession();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Verifica se há um administrador logado e ativo
     *
     * @return bool
     */
    public static function check(): bool
    {
        self::startSession();

        if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            return false;
        }

        // Timeout por inatividade (2 horas padrão)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
            self::logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Retorna os dados do administrador atualmente autenticado
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'    => $_SESSION['admin_id'] ?? null,
            'nome'  => $_SESSION['admin_nome'] ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
        ];
    }

    /**
     * Exige que o usuário esteja autenticado para prosseguir na rota
     * Redireciona para tela de login caso contrário
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'Você precisa estar logado para acessar esta área.';
            header('Location: /sce/public/login');
            exit;
        }
    }
}
