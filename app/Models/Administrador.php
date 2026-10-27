<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Models/Administrador.php
 * DESCRIÇÃO: Modelo responsável pela persistência e autenticação de administradores
 * =====================================================================
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class Administrador
{
    /**
     * Busca um administrador pelo endereço de e-mail
     *
     * @param string $email
     * @return array|null
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca um administrador pelo ID
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, nome, email, ativo, criado_em, atualizado_em FROM administradores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Cadastra um novo administrador no sistema
     *
     * @param array $data ['nome' => ..., 'email' => ..., 'senha' => ...]
     * @return int ID inserido
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $hash = password_hash($data['senha'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO administradores (nome, email, senha_hash, ativo, criado_em)
            VALUES (:nome, :email, :senha_hash, 1, NOW())
        ");

        $stmt->execute([
            ':nome'       => $data['nome'],
            ':email'      => $data['email'],
            ':senha_hash' => $hash,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Verifica as credenciais de e-mail e senha
     *
     * @param string $email
     * @param string $senha
     * @return array|false Retorna os dados do admin se válido e ativo, ou false
     */
    public static function authenticate(string $email, string $senha): array|false
    {
        $admin = self::findByEmail($email);
        if (!$admin) {
            return false;
        }

        // Verifica se a conta está ativa
        if ((int)$admin['ativo'] !== 1) {
            return false;
        }

        // Verifica a senha criptografada
        if (password_verify($senha, $admin['senha_hash'])) {
            return $admin;
        }

        return false;
    }

    /**
     * Retorna a contagem total de administradores
     *
     * @return int
     */
    public static function count(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza dados cadastrais e opcionalmente a senha de um administrador
     *
     * @param int $id
     * @param array $data ['nome' => ..., 'email' => ..., 'nova_senha' => ...]
     * @return bool
     */
    public static function updateProfile(int $id, array $data): bool
    {
        $pdo = Database::getConnection();

        if (!empty($data['nova_senha'])) {
            $hash = password_hash($data['nova_senha'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE administradores
                SET nome = :nome, email = :email, senha_hash = :senha_hash, atualizado_em = NOW()
                WHERE id = :id
            ");
            return $stmt->execute([
                ':nome'       => $data['nome'],
                ':email'      => $data['email'],
                ':senha_hash' => $hash,
                ':id'         => $id,
            ]);
        }

        $stmt = $pdo->prepare("
            UPDATE administradores
            SET nome = :nome, email = :email, atualizado_em = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':nome'  => $data['nome'],
            ':email' => $data['email'],
            ':id'    => $id,
        ]);
    }
}
