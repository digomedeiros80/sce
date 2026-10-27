<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Models/Emprestimo.php
 * DESCRIÇÃO: Modelo principal do sistema: Gerenciamento transacional de empréstimos e devoluções
 * =====================================================================
 */

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Emprestimo
{
    /**
     * Retorna lista paginada de empréstimos com relacionamentos e filtros
     *
     * @param int|null $usuarioId Filtro por usuário
     * @param int|null $equipamentoId Filtro por equipamento
     * @param string $status Filtro por status (ABERTO, DEVOLVIDO, ATRASADO, TODOS)
     * @param string $dataInicio Filtro por data inicial
     * @param string $dataFim Filtro por data final
     * @param int $page Página
     * @param int $perPage Itens por página
     * @return array
     */
    public static function paginate(
        ?int $usuarioId = null,
        ?int $equipamentoId = null,
        string $status = '',
        string $dataInicio = '',
        string $dataFim = '',
        int $page = 1,
        int $perPage = 10
    ): array {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($usuarioId) {
            $conditions[] = "e.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }

        if ($equipamentoId) {
            $conditions[] = "e.equipamento_id = :equipamento_id";
            $params[':equipamento_id'] = $equipamentoId;
        }

        if ($status === 'ABERTO') {
            $conditions[] = "e.status = 'ABERTO'";
        } elseif ($status === 'DEVOLVIDO') {
            $conditions[] = "e.status = 'DEVOLVIDO'";
        } elseif ($status === 'ATRASADO') {
            $conditions[] = "e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW()";
        }

        if ($dataInicio !== '') {
            $conditions[] = "DATE(e.data_emprestimo) >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== '') {
            $conditions[] = "DATE(e.data_emprestimo) <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Contagem
        $countSql = "SELECT COUNT(*) FROM emprestimos e {$where}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, max(1, $totalPages)));
        $offset = ($page - 1) * $perPage;

        // Seleção com joins para dados completos
        $sql = "
            SELECT
                e.*,
                u.nome AS usuario_nome,
                u.identificacao AS usuario_identificacao,
                u.tipo AS usuario_tipo,
                eq.nome AS equipamento_nome,
                eq.patrimonio AS equipamento_patrimonio,
                eq.categoria AS equipamento_categoria,
                a.nome AS administrador_nome,
                CASE
                    WHEN e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW() THEN 1
                    ELSE 0
                END AS atrasado
            FROM emprestimos e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN equipamentos eq ON eq.id = e.equipamento_id
            INNER JOIN administradores a ON a.id = e.administrador_id
            {$where}
            ORDER BY e.id DESC
            LIMIT :offset, :per_page
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
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
     * Busca dados detalhados de um empréstimo por ID
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT
                e.*,
                u.nome AS usuario_nome,
                u.email AS usuario_email,
                u.telefone AS usuario_telefone,
                u.identificacao AS usuario_identificacao,
                u.tipo AS usuario_tipo,
                eq.nome AS equipamento_nome,
                eq.patrimonio AS equipamento_patrimonio,
                eq.categoria AS equipamento_categoria,
                eq.marca AS equipamento_marca,
                eq.modelo AS equipamento_modelo,
                a.nome AS administrador_nome,
                CASE
                    WHEN e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW() THEN 1
                    ELSE 0
                END AS atrasado
            FROM emprestimos e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN equipamentos eq ON eq.id = e.equipamento_id
            INNER JOIN administradores a ON a.id = e.administrador_id
            WHERE e.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Realiza o registro atômico de um empréstimo com transação SQL e bloqueio
     *
     * @param array $data ['usuario_id', 'equipamento_id', 'administrador_id', 'data_emprestimo', 'data_prevista_devolucao', 'observacoes']
     * @return int ID do empréstimo criado
     * @throws Exception Em caso de inconsistência de regras de negócio
     */
    public static function registrarEmprestimo(array $data): int
    {
        $pdo = Database::getConnection();
        $isMysql = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');
        $forUpdate = $isMysql ? ' FOR UPDATE' : '';

        // Inicia a transação atômica
        $pdo->beginTransaction();

        try {
            $usuarioId     = (int) $data['usuario_id'];
            $equipamentoId = (int) $data['equipamento_id'];
            $adminId       = (int) $data['administrador_id'];

            // 1. Valida se o usuário existe e está ativo
            $stmtUser = $pdo->prepare("SELECT id, nome, ativo FROM usuarios WHERE id = :id{$forUpdate}");
            $stmtUser->execute([':id' => $usuarioId]);
            $user = $stmtUser->fetch();

            if (!$user) {
                throw new Exception("Usuário solicitante não foi encontrado no sistema.");
            }
            if ((int)$user['ativo'] !== 1) {
                throw new Exception("O usuário '{$user['nome']}' está inativo e não pode realizar empréstimos.");
            }

            // 2. Valida se o equipamento existe e está rigorosamente DISPONIVEL (com bloqueio FOR UPDATE)
            $stmtEq = $pdo->prepare("SELECT id, nome, patrimonio, status FROM equipamentos WHERE id = :id{$forUpdate}");
            $stmtEq->execute([':id' => $equipamentoId]);
            $equipamento = $stmtEq->fetch();

            if (!$equipamento) {
                throw new Exception("Equipamento selecionado não foi encontrado no sistema.");
            }
            if ($equipamento['status'] !== 'DISPONIVEL') {
                throw new Exception("O equipamento '{$equipamento['nome']}' ({$equipamento['patrimonio']}) não está disponível para empréstimo (Situação atual: {$equipamento['status']}).");
            }

            // 3. Verifica se não há empréstimo em aberto para este equipamento (dupla checagem de consistência)
            $stmtCheck = $pdo->prepare("SELECT id FROM emprestimos WHERE equipamento_id = :eq_id AND status = 'ABERTO' LIMIT 1{$forUpdate}");
            $stmtCheck->execute([':eq_id' => $equipamentoId]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Já existe uma operação de empréstimo em aberto para este equipamento.");
            }

            // 4. Validação das datas
            $dataEmp = $data['data_emprestimo'] ?? date('Y-m-d H:i:s');
            $dataPrev = $data['data_prevista_devolucao'];

            if (strtotime($dataPrev) < strtotime($dataEmp)) {
                throw new Exception("A data e hora prevista de devolução não pode ser anterior à data do empréstimo.");
            }

            // 5. Insere o registro de empréstimo
            $stmtInsert = $pdo->prepare("
                INSERT INTO emprestimos (
                    usuario_id, equipamento_id, administrador_id,
                    data_emprestimo, data_prevista_devolucao, observacoes, status
                ) VALUES (
                    :usuario_id, :equipamento_id, :administrador_id,
                    :data_emprestimo, :data_prevista_devolucao, :observacoes, 'ABERTO'
                )
            ");

            $stmtInsert->execute([
                ':usuario_id'               => $usuarioId,
                ':equipamento_id'           => $equipamentoId,
                ':administrador_id'         => $adminId,
                ':data_emprestimo'          => $dataEmp,
                ':data_prevista_devolucao'  => $dataPrev,
                ':observacoes'              => !empty($data['observacoes']) ? trim($data['observacoes']) : null,
            ]);

            $emprestimoId = (int) $pdo->lastInsertId();

            // 6. Atualiza o status do equipamento para EMPRESTADO
            $stmtUpdateEq = $pdo->prepare("UPDATE equipamentos SET status = 'EMPRESTADO', atualizado_em = NOW() WHERE id = :id");
            $stmtUpdateEq->execute([':id' => $equipamentoId]);

            // Confirma a transação com sucesso
            $pdo->commit();

            return $emprestimoId;
        } catch (Exception $e) {
            // Em caso de qualquer falha, desfaz todas as alterações
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Registra a devolução do equipamento dentro de uma transação SQL
     *
     * @param int $emprestimoId ID do empréstimo
     * @param string|null $dataDevolucao Data/hora da devolução (padrão NOW())
     * @param string|null $observacoes Observações da devolução
     * @param string $situacaoFinal Equipamento volta para 'DISPONIVEL' ou vai para 'MANUTENCAO'
     * @return bool
     * @throws Exception Caso o empréstimo já tenha sido devolvido ou não exista
     */
    public static function registrarDevolucao(
        int $emprestimoId,
        ?string $dataDevolucao = null,
        ?string $observacoes = null,
        string $situacaoFinal = 'DISPONIVEL'
    ): bool {
        $pdo = Database::getConnection();
        $isMysql = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');
        $forUpdate = $isMysql ? ' FOR UPDATE' : '';

        $pdo->beginTransaction();

        try {
            // 1. Busca e bloqueia o empréstimo para atualização concorrente
            $stmtEmp = $pdo->prepare("SELECT * FROM emprestimos WHERE id = :id{$forUpdate}");
            $stmtEmp->execute([':id' => $emprestimoId]);
            $emp = $stmtEmp->fetch();

            if (!$emp) {
                throw new Exception("Registro de empréstimo não encontrado.");
            }

            // 2. Impede devoluções duplicadas
            if ($emp['status'] === 'DEVOLVIDO') {
                throw new Exception("Este empréstimo já foi finalizado anteriormente e não pode receber nova devolução.");
            }

            $equipamentoId = (int) $emp['equipamento_id'];
            $dataDev = !empty($dataDevolucao) ? $dataDevolucao : date('Y-m-d H:i:s');

            // 3. Atualiza o status do empréstimo para DEVOLVIDO
            $stmtUpEmp = $pdo->prepare("
                UPDATE emprestimos
                SET status = 'DEVOLVIDO',
                    data_devolucao = :data_devolucao,
                    observacoes_devolucao = :obs
                WHERE id = :id
            ");

            $stmtUpEmp->execute([
                ':data_devolucao' => $dataDev,
                ':obs'            => !empty($observacoes) ? trim($observacoes) : null,
                ':id'             => $emprestimoId,
            ]);

            // 4. Define o novo status do equipamento: DISPONIVEL ou MANUTENCAO (caso tenha avarias/defeito)
            $novoStatus = ($situacaoFinal === 'MANUTENCAO') ? 'MANUTENCAO' : 'DISPONIVEL';

            $stmtUpEq = $pdo->prepare("UPDATE equipamentos SET status = :status, atualizado_em = NOW() WHERE id = :id");
            $stmtUpEq->execute([
                ':status' => $novoStatus,
                ':id'     => $equipamentoId,
            ]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Retorna a contagem de empréstimos em atraso (ABERTO e com data prevista vencida)
     *
     * @return int
     */
    public static function countAtrasados(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'ABERTO' AND data_prevista_devolucao < NOW()");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna os últimos empréstimos para alimentar o Dashboard
     *
     * @param int $limit
     * @return array
     */
    public static function getLatest(int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT
                e.id,
                e.data_emprestimo,
                e.data_prevista_devolucao,
                e.data_devolucao,
                e.status,
                u.nome AS usuario_nome,
                eq.nome AS equipamento_nome,
                eq.patrimonio AS equipamento_patrimonio,
                CASE
                    WHEN e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW() THEN 1
                    ELSE 0
                END AS atrasado
            FROM emprestimos e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN equipamentos eq ON eq.id = e.equipamento_id
            ORDER BY e.id DESC
            LIMIT :lim
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna dados para relatórios consolidados com filtros flexíveis
     *
     * @param array $filtros ['tipo', 'usuario_id', 'data_inicio', 'data_fim', 'categoria', 'status']
     * @return array
     */
    public static function getReportData(array $filtros): array
    {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        $tipoRelatorio = $filtros['tipo'] ?? 'todos';

        if ($tipoRelatorio === 'abertos') {
            $conditions[] = "e.status = 'ABERTO'";
        } elseif ($tipoRelatorio === 'atrasados') {
            $conditions[] = "e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW()";
        } elseif ($tipoRelatorio === 'devolvidos') {
            $conditions[] = "e.status = 'DEVOLVIDO'";
        }

        if (!empty($filtros['usuario_id'])) {
            $conditions[] = "e.usuario_id = :usuario_id";
            $params[':usuario_id'] = (int) $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $conditions[] = "DATE(e.data_emprestimo) >= :data_inicio";
            $params[':data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $conditions[] = "DATE(e.data_emprestimo) <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'];
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT
                e.id,
                e.data_emprestimo,
                e.data_prevista_devolucao,
                e.data_devolucao,
                e.status,
                e.observacoes,
                e.observacoes_devolucao,
                u.nome AS usuario_nome,
                u.identificacao AS usuario_identificacao,
                u.tipo AS usuario_tipo,
                eq.nome AS equipamento_nome,
                eq.patrimonio AS equipamento_patrimonio,
                eq.categoria AS equipamento_categoria,
                a.nome AS administrador_nome,
                CASE
                    WHEN e.status = 'ABERTO' AND e.data_prevista_devolucao < NOW() THEN 'ATRASADO'
                    ELSE e.status
                END AS situacao_calculada
            FROM emprestimos e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN equipamentos eq ON eq.id = e.equipamento_id
            INNER JOIN administradores a ON a.id = e.administrador_id
            {$where}
            ORDER BY e.data_emprestimo DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
