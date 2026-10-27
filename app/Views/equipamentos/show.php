<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/equipamentos/show.php
 * DESCRIÇÃO: Ficha de Detalhes do Equipamento
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas">
            <div class="card-header-clean">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-laptop fs-4 text-primary"></i>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($equipamento['nome']) ?></h5>
                        <small class="text-muted">Patrimônio: <?= htmlspecialchars($equipamento['patrimonio']) ?></small>
                    </div>
                </div>
                <div>
                    <?php if ($equipamento['status'] === 'DISPONIVEL'): ?>
                        <span class="badge-status badge-disponivel fs-6"><i class="bi bi-check-circle"></i> Disponível</span>
                    <?php elseif ($equipamento['status'] === 'EMPRESTADO'): ?>
                        <span class="badge-status badge-emprestado fs-6"><i class="bi bi-arrow-up-right"></i> Emprestado</span>
                    <?php elseif ($equipamento['status'] === 'MANUTENCAO'): ?>
                        <span class="badge-status badge-manutencao fs-6"><i class="bi bi-tools"></i> Manutenção</span>
                    <?php else: ?>
                        <span class="badge-status badge-inativo fs-6"><i class="bi bi-slash-circle"></i> Inativo</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Categoria</label>
                        <div class="fw-medium text-dark"><?= htmlspecialchars($equipamento['categoria'] ?? 'Não especificada') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Número de Patrimônio</label>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($equipamento['patrimonio']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Marca / Fabricante</label>
                        <div class="fw-medium"><?= htmlspecialchars($equipamento['marca'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Modelo</label>
                        <div class="fw-medium"><?= htmlspecialchars($equipamento['modelo'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Número de Série</label>
                        <div class="fw-medium"><?= htmlspecialchars($equipamento['numero_serie'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Data de Cadastro</label>
                        <div class="fw-medium"><?= date('d/m/Y H:i', strtotime($equipamento['criado_em'])) ?></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Descrição e Acessórios</label>
                        <div class="p-3 bg-light rounded border text-secondary">
                            <?= !empty($equipamento['descricao']) ? nl2br(htmlspecialchars($equipamento['descricao'])) : '<em>Nenhuma observação cadastrada.</em>' ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="/sce/public/equipamentos" class="btn btn-light border">
                        <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                    </a>
                    <div class="d-flex gap-2">
                        <a href="/sce/public/equipamentos/editar?id=<?= $equipamento['id'] ?>" class="btn btn-primary btn-sce">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <?php if ($equipamento['status'] === 'DISPONIVEL'): ?>
                            <a href="/sce/public/emprestimos/novo" class="btn btn-success">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Emprestar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
