<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/relatorios/index.php
 * DESCRIÇÃO: Painel de Relatórios Gerenciais com Filtros, Impressão CSS e Exportação CSV
 * =====================================================================
 */
?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h5 class="fw-bold mb-1">Relatórios Gerenciais</h5>
        <p class="text-muted small mb-0">Total de <?= count($dados) ?> registro(s) encontrado(s)</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print();" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-printer"></i> Imprimir Relatório
        </button>
        <a href="/sce/public/relatorios/exportar?tipo=<?= urlencode($tipo) ?>&usuario_id=<?= $usuarioId ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn btn-success d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i> Exportar CSV
        </a>
    </div>
</div>

<!-- FILTROS GERENCIAIS -->
<div class="card card-saas mb-4 no-print">
    <div class="card-body p-3">
        <form action="/sce/public/relatorios" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="tipo" class="form-label small">Tipo de Consulta</label>
                <select class="form-select form-select-sm" id="tipo" name="tipo">
                    <option value="todos" <?= ($tipo === 'todos') ? 'selected' : '' ?>>Todos os Empréstimos</option>
                    <option value="abertos" <?= ($tipo === 'abertos') ? 'selected' : '' ?>>Somente Em Aberto</option>
                    <option value="atrasados" <?= ($tipo === 'atrasados') ? 'selected' : '' ?>>Somente em Atraso (Vencidos)</option>
                    <option value="devolvidos" <?= ($tipo === 'devolvidos') ? 'selected' : '' ?>>Empréstimos Devolvidos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="usuario_id" class="form-label small">Filtrar por Usuário</label>
                <select class="form-select form-select-sm" id="usuario_id" name="usuario_id">
                    <option value="">Todos os Usuários</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($usuarioId == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="data_inicio" class="form-label small">Período De</label>
                <input type="date" class="form-control form-control-sm" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>">
            </div>

            <div class="col-md-2">
                <label for="data_fim" class="form-label small">Até</label>
                <input type="date" class="form-control form-control-sm" id="data_fim" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Gerar Relatório">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="/sce/public/relatorios" class="btn btn-sm btn-light border" title="Limpar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TABELA DE RESULTADOS DO RELATÓRIO -->
<div class="card card-saas">
    <div class="table-responsive">
        <table class="table table-modern table-bordered">
            <thead>
                <tr>
                    <th style="width: 50px;">Cód.</th>
                    <th>Equipamento</th>
                    <th>Patrimônio</th>
                    <th>Categoria</th>
                    <th>Usuário Solicitante</th>
                    <th>Tipo</th>
                    <th>Retirada</th>
                    <th>Previsão</th>
                    <th>Devolução</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            Nenhum registro localizado para o período e critérios solicitados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dados as $item): ?>
                        <tr>
                            <td class="text-muted">#<?= $item['id'] ?></td>
                            <td class="fw-medium text-dark"><?= htmlspecialchars($item['equipamento_nome']) ?></td>
                            <td><?= htmlspecialchars($item['equipamento_patrimonio']) ?></td>
                            <td><?= htmlspecialchars($item['equipamento_categoria'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['usuario_nome']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($item['usuario_tipo']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($item['data_emprestimo'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($item['data_prevista_devolucao'])) ?></td>
                            <td>
                                <?= !empty($item['data_devolucao']) ? date('d/m/Y H:i', strtotime($item['data_devolucao'])) : '<span class="text-muted">Pendente</span>' ?>
                            </td>
                            <td>
                                <?php if ($item['situacao_calculada'] === 'DEVOLVIDO'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Devolvido</span>
                                <?php elseif ($item['situacao_calculada'] === 'ATRASADO'): ?>
                                    <span class="badge bg-danger text-white">Atrasado</span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Aberto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
