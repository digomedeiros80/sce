<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Models/Usuario.php
 * DESCRIÇÃO: Modelo responsável pela gestão dos usuários (Alunos, Professores, etc)
 * =====================================================================
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario
{
    /**
     * Retorna a lista paginada de usuários com filtros de pesquisa
     *
     * @param string $busca Termo de busca por nome ou identificação
     * @param string $tipo Filtro por tipo (ALUNO, PROFESSOR, FUNCIONARIO, etc)
     * @param int $page Página atual
     * @param int $perPage Quantidade por página
     * @return array ['items' => array, 'total' => int, 'pages' => int, 'current' => int]
     */
    public static function paginate(string $busca = '', string $tipo = '', int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($busca !== '') {
            $conditions[] = "(nome LIKE :busca_nome OR identificacao LIKE :busca_id OR email LIKE :busca_email)";
            $params[':busca_nome']  = "%{$busca}%";
            $params[':busca_id']    = "%{$busca}%";
            $params[':busca_email'] = "%{$busca}%";
        }

        if ($tipo !== '' && $tipo !== 'TODOS') {
            $conditions[] = "tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Contagem total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, max(1, $totalPages)));
        $offset = ($page - 1) * $perPage;

        // Consulta de registros
        $sql = "SELECT * FROM usuarios {$where} ORDER BY nome ASC LIMIT :offset, :per_page";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'   => $stmt->fetchAll(),
            'total'   => $total,
            'pages'   => $totalPages,
            'current' => $page,
        ];
    }

    /**
     * Busca usuário pelo ID
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retorna todos os usuários ativos para seleção em formulários
     *
     * @return array
     */
    public static function getActiveList(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nome, tipo, identificacao FROM usuarios WHERE ativo = 1 ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    /**
     * Cadastra um novo usuário
     *
     * @param array $data
     * @return int ID inserido
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, telefone, identificacao, tipo, ativo, criado_em)
            VALUES (:nome, :email, :telefone, :identificacao, :tipo, :ativo, NOW())
        ");

        $stmt->execute([
            ':nome'          => $data['nome'],
            ':email'         => !empty($data['email']) ? $data['email'] : null,
            ':telefone'      => !empty($data['telefone']) ? $data['telefone'] : null,
            ':identificacao' => !empty($data['identificacao']) ? $data['identificacao'] : null,
            ':tipo'          => $data['tipo'] ?? 'ALUNO',
            ':ativo'         => isset($data['ativo']) ? (int)$data['ativo'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um usuário
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nome = :nome,
                email = :email,
                telefone = :telefone,
                identificacao = :identificacao,
                tipo = :tipo,
                ativo = :ativo,
                atualizado_em = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nome'          => $data['nome'],
            ':email'         => !empty($data['email']) ? $data['email'] : null,
            ':telefone'      => !empty($data['telefone']) ? $data['telefone'] : null,
            ':identificacao' => !empty($data['identificacao']) ? $data['identificacao'] : null,
            ':tipo'          => $data['tipo'] ?? 'ALUNO',
            ':ativo'         => isset($data['ativo']) ? (int)$data['ativo'] : 1,
            ':id'            => $id,
        ]);
    }

    /**
     * Inativa um usuário caso ele não possua empréstimos em aberto
     *
     * @param int $id
     * @return bool
     * @throws \Exception Caso possua empréstimo aberto
     */
    public static function inactivate(int $id): bool
    {
        $pdo = Database::getConnection();

        // Regra de negócio: Não permitir inativação com empréstimos em aberto
        if (self::hasOpenLoans($id)) {
            throw new \Exception("Não é permitido inativar um usuário que possui empréstimos em aberto.");
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0, atualizado_em = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Ativa um usuário inativado
     *
     * @param int $id
     * @return bool
     */
    public static function activate(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 1, atualizado_em = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica se o usuário possui algum empréstimo com status ABERTO
     *
     * @param int $id
     * @return bool
     */
    public static function hasOpenLoans(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE usuario_id = :id AND status = 'ABERTO'");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Contagem de usuários ativos no sistema
     *
     * @return int
     */
    public static function countActive(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE ativo = 1");
        return (int) $stmt->fetchColumn();
    }
}
