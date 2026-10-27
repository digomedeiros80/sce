<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/usuarios/edit.php
 * DESCRIÇÃO: Formulário de Edição de Usuário com Bloqueio de Inativação caso tenha Empréstimos em Aberto
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Editar Usuário: <?= htmlspecialchars($usuario['nome']) ?></h5>
            </div>

            <div class="card-body p-4">
                <?php if ($hasOpenLoans): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            Este usuário possui <strong>empréstimos em aberto</strong>. Ele não pode ser inativado enquanto não regularizar e devolver os equipamentos pendentes.
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/sce/public/usuarios/editar" method="POST">
                    <?= \App\Core\Csrf::input() ?>
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo de Solicitante <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="ALUNO" <?= ($usuario['tipo'] === 'ALUNO') ? 'selected' : '' ?>>Aluno</option>
                                <option value="PROFESSOR" <?= ($usuario['tipo'] === 'PROFESSOR') ? 'selected' : '' ?>>Professor</option>
                                <option value="FUNCIONARIO" <?= ($usuario['tipo'] === 'FUNCIONARIO') ? 'selected' : '' ?>>Funcionário</option>
                                <option value="OUTRO" <?= ($usuario['tipo'] === 'OUTRO') ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="identificacao" class="form-label">Identificação / Matrícula</label>
                            <input type="text" class="form-control" id="identificacao" name="identificacao" value="<?= htmlspecialchars($usuario['identificacao'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone / WhatsApp</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                        </div>

                        <div class="col-md-8">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="ativo" class="form-label">Situação Cadastral</label>
                            <select class="form-select" id="ativo" name="ativo" <?= $hasOpenLoans ? 'disabled' : '' ?>>
                                <option value="1" <?= ((int)$usuario['ativo'] === 1) ? 'selected' : '' ?>>Ativo</option>
                                <option value="0" <?= ((int)$usuario['ativo'] === 0) ? 'selected' : '' ?>>Inativo</option>
                            </select>
                            <?php if ($hasOpenLoans): ?>
                                <input type="hidden" name="ativo" value="1">
                                <div class="form-text text-danger small">Bloqueado para inativação por pendências.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/sce/public/usuarios" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce">
                            <i class="bi bi-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
