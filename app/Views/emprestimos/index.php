<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/emprestimos/index.php
 * DESCRIÇÃO: Listagem Geral de Empréstimos com Filtros por Usuário, Equipamento, Período e Situação
 * =====================================================================
 */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-1">Controle de Empréstimos</h5>
        <p class="text-muted small mb-0">Total de <?= number_format($total, 0, ',', '.') ?> registro(s) no histórico</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/sce/public/devolucoes" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-box-arrow-in-down-left"></i> Devoluções
        </a>
        <a href="/sce/public/emprestimos/novo" class="btn btn-sce d-inline-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Novo Empréstimo
        </a>
    </div>
</div>

<!-- FILTROS DE CONSULTA -->
<div class="card card-saas mb-4">
    <div class="card-body p-3">
        <form action="/sce/public/emprestimos" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="usuario_id" class="form-label small">Usuário Solicitante</label>
                <select class="form-select form-select-sm" id="usuario_id" name="usuario_id">
                    <option value="">Todos os Usuários</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($usuarioId == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label small">Situação do Empréstimo</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">Todas as Situações</option>
                    <option value="ABERTO" <?= ($status === 'ABERTO') ? 'selected' : '' ?>>Em Aberto</option>
                    <option value="ATRASADO" <?= ($status === 'ATRASADO') ? 'selected' : '' ?>>Em Atraso (Vencido)</option>
                    <option value="DEVOLVIDO" <?= ($status === 'DEVOLVIDO') ? 'selected' : '' ?>>Devolvido</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="data_inicio" class="form-label small">Data Inicial</label>
                <input type="date" class="form-control form-control-sm" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>">
            </div>

            <div class="col-md-2">
                <label for="data_fim" class="form-label small">Data Final</label>
                <input type="date" class="form-control form-control-sm" id="data_fim" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Filtrar">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="/sce/public/emprestimos" class="btn btn-sm btn-light border" title="Limpar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TABELA DE EMPRÉSTIMOS -->
<div class="card card-saas">
    <div class="table-responsive">
        <table class="table table-modern table-hover">
            <thead>
                <tr>
                    <th style="width: 70px;">Cód.</th>
                    <th>Equipamento</th>
                    <th>Patrimônio</th>
                    <th>Usuário</th>
                    <th>Data Retirada</th>
                    <th>Previsão Devolução</th>
                    <th>Data Devolução</th>
                    <th>Situação</th>
                    <th class="text-end" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($emprestimos)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-1"></i>
                            Nenhum registro de empréstimo encontrado para os filtros selecionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($emprestimos as $emp): ?>
                        <tr class="<?= ($emp['atrasado'] == 1) ? 'table-danger' : '' ?>">
                            <td class="fw-bold text-muted">#<?= $emp['id'] ?></td>
                            <td class="fw-medium text-dark"><?= htmlspecialchars($emp['equipamento_nome']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($emp['equipamento_patrimonio']) ?></span></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($emp['usuario_nome']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($emp['usuario_tipo']) ?></small>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($emp['data_emprestimo'])) ?></td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($emp['data_prevista_devolucao'])) ?>
                            </td>
                            <td>
                                <?php if (!empty($emp['data_devolucao'])): ?>
                                    <span class="text-success"><i class="bi bi-check2"></i> <?= date('d/m/Y H:i', strtotime($emp['data_devolucao'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['status'] === 'DEVOLVIDO'): ?>
                                    <span class="badge-status badge-devolvido"><i class="bi bi-check2-all"></i> Devolvido</span>
                                <?php elseif ($emp['atrasado'] == 1): ?>
                                    <span class="badge-status badge-atrasado"><i class="bi bi-exclamation-triangle-fill"></i> Em Atraso</span>
                                <?php else: ?>
                                    <span class="badge-status badge-emprestado"><i class="bi bi-clock"></i> Em Aberto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($emp['status'] === 'ABERTO'): ?>
                                    <a href="/sce/public/devolucoes?id=<?= $emp['id'] ?>" class="btn btn-sm btn-success py-1 px-2" title="Registrar Devolução">
                                        <i class="bi bi-box-arrow-in-down-left"></i> Devolver
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Concluído</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINAÇÃO -->
    <?php if ($pages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3 border-top">
            <small class="text-muted">Página <?= $current ?> de <?= $pages ?></small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($current <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?usuario_id=<?= $usuarioId ?>&status=<?= urlencode($status) ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>&pagina=<?= $current - 1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i === $current) ? 'active' : '' ?>">
                            <a class="page-link" href="?usuario_id=<?= $usuarioId ?>&status=<?= urlencode($status) ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>&pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($current >= $pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?usuario_id=<?= $usuarioId ?>&status=<?= urlencode($status) ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>&pagina=<?= $current + 1 ?>">Próxima</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
