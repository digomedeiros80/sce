<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Models/Equipamento.php
 * DESCRIÇÃO: Modelo responsável pelo gerenciamento de patrimônios e status de equipamentos
 * =====================================================================
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class Equipamento
{
    /**
     * Retorna a lista paginada de equipamentos com filtros avançados
     *
     * @param string $busca Termo de busca por nome, modelo ou número de série
     * @param string $patrimonio Filtro exato ou parcial por código de patrimônio
     * @param string $categoria Filtro por categoria
     * @param string $status Filtro por situação (DISPONIVEL, EMPRESTADO, MANUTENCAO, INATIVO)
     * @param int $page Página atual
     * @param int $perPage Quantidade por página
     * @return array ['items' => array, 'total' => int, 'pages' => int, 'current' => int]
     */
    public static function paginate(
        string $busca = '',
        string $patrimonio = '',
        string $categoria = '',
        string $status = '',
        int $page = 1,
        int $perPage = 10
    ): array {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($busca !== '') {
            $conditions[] = "(nome LIKE :busca_nome OR modelo LIKE :busca_mod OR marca LIKE :busca_mar)";
            $params[':busca_nome'] = "%{$busca}%";
            $params[':busca_mod']  = "%{$busca}%";
            $params[':busca_mar']  = "%{$busca}%";
        }

        if ($patrimonio !== '') {
            $conditions[] = "patrimonio LIKE :patrimonio";
            $params[':patrimonio'] = "%{$patrimonio}%";
        }

        if ($categoria !== '' && $categoria !== 'TODAS') {
            $conditions[] = "categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        if ($status !== '' && $status !== 'TODOS') {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Contagem total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM equipamentos {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, max(1, $totalPages)));
        $offset = ($page - 1) * $perPage;

        // Consulta de dados com ordenação
        $sql = "SELECT * FROM equipamentos {$where} ORDER BY nome ASC LIMIT :offset, :per_page";
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
     * Busca um equipamento pelo ID
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipamentos WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca um equipamento pelo código patrimonial
     *
     * @param string $patrimonio
     * @return array|null
     */
    public static function findByPatrimonio(string $patrimonio): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipamentos WHERE patrimonio = :patrimonio LIMIT 1");
        $stmt->execute([':patrimonio' => $patrimonio]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retorna a lista de categorias distintas cadastradas para filtros e selects
     *
     * @return array
     */
    public static function getDistinctCategories(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT categoria FROM equipamentos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Retorna a lista de equipamentos disponíveis para novo empréstimo
     *
     * @return array
     */
    public static function getAvailableList(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nome, patrimonio, categoria FROM equipamentos WHERE status = 'DISPONIVEL' ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    /**
     * Cadastra um novo equipamento
     *
     * @param array $data
     * @return int ID inserido
     * @throws \Exception Caso patrimônio já exista
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        // Validação de unicidade de patrimônio
        if (self::findByPatrimonio($data['patrimonio'])) {
            throw new \Exception("Já existe um equipamento cadastrado com o patrimônio informado: {$data['patrimonio']}");
        }

        $stmt = $pdo->prepare("
            INSERT INTO equipamentos (nome, descricao, patrimonio, categoria, marca, modelo, numero_serie, status, criado_em)
            VALUES (:nome, :descricao, :patrimonio, :categoria, :marca, :modelo, :numero_serie, :status, NOW())
        ");

        $stmt->execute([
            ':nome'         => $data['nome'],
            ':descricao'    => !empty($data['descricao']) ? $data['descricao'] : null,
            ':patrimonio'   => $data['patrimonio'],
            ':categoria'    => !empty($data['categoria']) ? $data['categoria'] : null,
            ':marca'        => !empty($data['marca']) ? $data['marca'] : null,
            ':modelo'       => !empty($data['modelo']) ? $data['modelo'] : null,
            ':numero_serie' => !empty($data['numero_serie']) ? $data['numero_serie'] : null,
            ':status'       => $data['status'] ?? 'DISPONIVEL',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um equipamento
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();

        // Checagem de patrimônio duplicado em outro ID
        $existente = self::findByPatrimonio($data['patrimonio']);
        if ($existente && (int)$existente['id'] !== $id) {
            throw new \Exception("O patrimônio informado já está em uso por outro equipamento.");
        }

        // Regra de consistência: Não permitir alteração manual para DISPONIVEL se houver empréstimo aberto
        if (isset($data['status']) && $data['status'] === 'DISPONIVEL') {
            if (self::hasOpenLoan($id)) {
                throw new \Exception("Não é permitido alterar a situação para 'DISPONÍVEL' pois o equipamento possui um empréstimo em aberto.");
            }
        }

        // Regra de consistência: Não permitir inativar equipamento com empréstimo aberto
        if (isset($data['status']) && $data['status'] === 'INATIVO') {
            if (self::hasOpenLoan($id)) {
                throw new \Exception("Não é permitido inativar um equipamento que possui empréstimo em aberto.");
            }
        }

        $stmt = $pdo->prepare("
            UPDATE equipamentos
            SET nome = :nome,
                descricao = :descricao,
                patrimonio = :patrimonio,
                categoria = :categoria,
                marca = :marca,
                modelo = :modelo,
                numero_serie = :numero_serie,
                status = :status,
                atualizado_em = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nome'         => $data['nome'],
            ':descricao'    => !empty($data['descricao']) ? $data['descricao'] : null,
            ':patrimonio'   => $data['patrimonio'],
            ':categoria'    => !empty($data['categoria']) ? $data['categoria'] : null,
            ':marca'        => !empty($data['marca']) ? $data['marca'] : null,
            ':modelo'       => !empty($data['modelo']) ? $data['modelo'] : null,
            ':numero_serie' => !empty($data['numero_serie']) ? $data['numero_serie'] : null,
            ':status'       => $data['status'],
            ':id'           => $id,
        ]);
    }

    /**
     * Altera o status do equipamento
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public static function setStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE equipamentos SET status = :status, atualizado_em = NOW() WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Inativa um equipamento caso não tenha empréstimo aberto
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public static function inactivate(int $id): bool
    {
        if (self::hasOpenLoan($id)) {
            throw new \Exception("Não é permitido inativar um equipamento que possui empréstimo em aberto.");
        }

        return self::setStatus($id, 'INATIVO');
    }

    /**
     * Verifica se o equipamento possui empréstimo ativo/aberto
     *
     * @param int $id
     * @return bool
     */
    public static function hasOpenLoan(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE equipamento_id = :id AND status = 'ABERTO'");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Retorna indicadores numéricos para o Dashboard
     *
     * @return array
     */
    public static function getMetrics(): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'DISPONIVEL' THEN 1 ELSE 0 END) AS disponiveis,
                SUM(CASE WHEN status = 'EMPRESTADO' THEN 1 ELSE 0 END) AS emprestados,
                SUM(CASE WHEN status = 'MANUTENCAO' THEN 1 ELSE 0 END) AS manutencao,
                SUM(CASE WHEN status = 'INATIVO' THEN 1 ELSE 0 END) AS inativos
            FROM equipamentos
        ";
        $stmt = $pdo->query($sql);
        $res = $stmt->fetch();

        return [
            'total'       => (int)($res['total'] ?? 0),
            'disponiveis' => (int)($res['disponiveis'] ?? 0),
            'emprestados' => (int)($res['emprestados'] ?? 0),
            'manutencao'  => (int)($res['manutencao'] ?? 0),
            'inativos'    => (int)($res['inativos'] ?? 0),
        ];
    }
}
