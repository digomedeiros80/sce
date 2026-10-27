<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Csrf.php
 * DESCRIÇÃO: Gerenciamento e verificação de tokens CSRF para formulários
 * =====================================================================
 */

namespace App\Core;

class Csrf
{
    /**
     * Gera ou recupera o token CSRF atual da sessão
     *
     * @return string Token CSRF criptograficamente seguro
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Renderiza o campo de input HTML oculto com o token CSRF
     *
     * @return string Tag HTML <input>
     */
    public static function input(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valida se o token enviado corresponde ao token gravado na sessão
     *
     * @param string|null $token Token recebido via POST ou cabeçalho
     * @return bool True se o token for válido, False caso contrário
     */
    public static function validate(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        // Comparação em tempo constante para mitigar ataques de timing
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
