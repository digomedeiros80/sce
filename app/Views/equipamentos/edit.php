<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/equipamentos/edit.php
 * DESCRIÇÃO: Formulário de Edição de Equipamento com Bloqueios de Consistência
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Editar Equipamento: <?= htmlspecialchars($equipamento['nome']) ?></h5>
            </div>

            <div class="card-body p-4">
                <?php if ($hasOpenLoan): ?>
                    <div class="alert alert-info d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Este equipamento possui um <strong>empréstimo em aberto</strong> neste momento. Por segurança e consistência, a situação não pode ser alterada para "Disponível" nem "Inativo" enquanto não houver devolução formal.
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/sce/public/equipamentos/editar" method="POST">
                    <?= \App\Core\Csrf::input() ?>
                    <input type="hidden" name="id" value="<?= $equipamento['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome do Equipamento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($equipamento['nome']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="patrimonio" class="form-label">Nº Patrimônio <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="patrimonio" name="patrimonio" value="<?= htmlspecialchars($equipamento['patrimonio']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="categoria" class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="categoria" name="categoria" list="lista-categorias" value="<?= htmlspecialchars($equipamento['categoria'] ?? '') ?>">
                            <datalist id="lista-categorias">
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label for="marca" class="form-label">Marca / Fabricante</label>
                            <input type="text" class="form-control" id="marca" name="marca" value="<?= htmlspecialchars($equipamento['marca'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars($equipamento['modelo'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="numero_serie" class="form-label">Número de Série</label>
                            <input type="text" class="form-control" id="numero_serie" name="numero_serie" value="<?= htmlspecialchars($equipamento['numero_serie'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Situação</label>
                            <select class="form-select" id="status" name="status">
                                <option value="DISPONIVEL" <?= ($equipamento['status'] === 'DISPONIVEL') ? 'selected' : '' ?> <?= $hasOpenLoan ? 'disabled' : '' ?>>Disponível</option>
                                <option value="EMPRESTADO" <?= ($equipamento['status'] === 'EMPRESTADO') ? 'selected' : '' ?> <?= !$hasOpenLoan ? 'disabled' : '' ?>>Emprestado</option>
                                <option value="MANUTENCAO" <?= ($equipamento['status'] === 'MANUTENCAO') ? 'selected' : '' ?>>Em Manutenção</option>
                                <option value="INATIVO" <?= ($equipamento['status'] === 'INATIVO') ? 'selected' : '' ?> <?= $hasOpenLoan ? 'disabled' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descricao" class="form-label">Especificações Técnicas / Observações</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($equipamento['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/sce/public/equipamentos" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce">
                            <i class="bi bi-save me-1"></i> Atualizar Equipamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
