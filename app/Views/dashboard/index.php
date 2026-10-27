<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/dashboard/index.php
 * DESCRIÇÃO: Visão do Painel Principal (Dashboard) com Cards de Métricas e Tabela de Empréstimos Recentes
 * =====================================================================
 */
?>

<!-- CARDS DE INDICADORES / METRICAS -->
<div class="row g-3 mb-4">
    <!-- Total de Equipamentos -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-laptop"></i>
            </div>
            <div class="metric-info">
                <h6>Total de Equipamentos</h6>
                <h3><?= number_format($metricsEquipamentos['total'], 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Equipamentos Disponíveis -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="metric-info">
                <h6>Disponíveis para Retirada</h6>
                <h3 class="text-success"><?= number_format($metricsEquipamentos['disponiveis'], 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Equipamentos Emprestados -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-info bg-opacity-10 text-primary">
                <i class="bi bi-arrow-up-right-circle"></i>
            </div>
            <div class="metric-info">
                <h6>Atualmente Emprestados</h6>
                <h3 class="text-primary"><?= number_format($metricsEquipamentos['emprestados'], 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Equipamentos em Manutenção -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-tools"></i>
            </div>
            <div class="metric-info">
                <h6>Em Manutenção / Reparo</h6>
                <h3 class="text-warning"><?= number_format($metricsEquipamentos['manutencao'], 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Empréstimos em Atraso -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-alarm"></i>
            </div>
            <div class="metric-info">
                <h6>Empréstimos em Atraso</h6>
                <h3 class="text-danger"><?= number_format($totalAtrasados, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Total de Usuários Ativos -->
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card shadow-sm">
            <div class="metric-icon-box bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-people"></i>
            </div>
            <div class="metric-info">
                <h6>Usuários Ativos (Alunos/Prof.)</h6>
                <h3><?= number_format($totalUsuariosAtivos, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- AÇÕES RÁPIDAS -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="/sce/public/emprestimos/novo" class="btn btn-sce d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Registrar Novo Empréstimo
    </a>
    <a href="/sce/public/devolucoes" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-in-down-left"></i> Registrar Devolução
    </a>
    <a href="/sce/public/equipamentos/novo" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="bi bi-laptop"></i> Cadastrar Equipamento
    </a>
    <a href="/sce/public/usuarios/novo" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="bi bi-person-plus"></i> Cadastrar Usuário
    </a>
</div>

<!-- TABELA COM ÚLTIMOS EMPRÉSTIMOS -->
<div class="card card-saas">
    <div class="card-header-clean">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history fs-5 text-primary"></i>
            <h6 class="mb-0 fw-bold">Últimos Empréstimos Registrados</h6>
        </div>
        <a href="/sce/public/emprestimos" class="btn btn-sm btn-link text-decoration-none">Ver Todos <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="table-responsive">
        <table class="table table-modern table-hover">
            <thead>
                <tr>
                    <th>Equipamento</th>
                    <th>Patrimônio</th>
                    <th>Usuário Solicitante</th>
                    <th>Data Retirada</th>
                    <th>Previsão Devolução</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimosEmprestimos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                            Nenhum empréstimo registrado até o momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ultimosEmprestimos as $emp): ?>
                        <tr>
                            <td class="fw-medium text-dark"><?= htmlspecialchars($emp['equipamento_nome']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($emp['equipamento_patrimonio']) ?></span></td>
                            <td><?= htmlspecialchars($emp['usuario_nome']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($emp['data_emprestimo'])) ?></td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($emp['data_prevista_devolucao'])) ?>
                            </td>
                            <td>
                                <?php if ($emp['status'] === 'DEVOLVIDO'): ?>
                                    <span class="badge-status badge-devolvido"><i class="bi bi-check2-circle"></i> Devolvido</span>
                                <?php elseif ($emp['atrasado'] == 1): ?>
                                    <span class="badge-status badge-atrasado"><i class="bi bi-exclamation-triangle"></i> Em Atraso</span>
                                <?php else: ?>
                                    <span class="badge-status badge-emprestado"><i class="bi bi-hourglass-split"></i> Em Aberto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($emp['status'] === 'ABERTO'): ?>
                                    <a href="/sce/public/devolucoes?id=<?= $emp['id'] ?>" class="btn btn-sm btn-success py-1 px-2" title="Registrar Devolução">
                                        <i class="bi bi-check-lg"></i> Devolver
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Finalizado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
