<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/emprestimos/create.php
 * DESCRIÇÃO: Formulário para Registro de Novo Empréstimo com Seleção de Equipamentos Disponíveis
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-box-arrow-up-right me-2 text-primary"></i>Registrar Saída / Empréstimo</h5>
            </div>

            <div class="card-body p-4">
                <?php if (empty($equipamentosDisponiveis)): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        <div>
                            <strong>Nenhum equipamento disponível no momento!</strong><br>
                            Todos os equipamentos estão emprestados, em manutenção ou inativos. Para registrar um novo empréstimo, aguarde uma devolução ou cadastre um novo item.
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($usuariosAtivos)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill fs-4"></i>
                        <div>
                            <strong>Nenhum usuário ativo encontrado!</strong><br>
                            É necessário ter pelo menos um usuário ativo para poder retirar equipamentos.
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/sce/public/emprestimos/novo" method="POST">
                    <?= \App\Core\Csrf::input() ?>

                    <div class="row g-3">
                        <!-- Seleção do Usuário -->
                        <div class="col-md-12">
                            <label for="usuario_id" class="form-label">Usuário Solicitante (Aluno / Professor / Funcionário) <span class="text-danger">*</span></label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required <?= empty($usuariosAtivos) ? 'disabled' : '' ?>>
                                <option value="">-- Selecione o usuário ativo --</option>
                                <?php foreach ($usuariosAtivos as $user): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['nome']) ?> — <?= htmlspecialchars($user['tipo']) ?> <?= !empty($user['identificacao']) ? '(' . htmlspecialchars($user['identificacao']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Seleção do Equipamento -->
                        <div class="col-md-12">
                            <label for="equipamento_id" class="form-label">Equipamento Disponível <span class="text-danger">*</span></label>
                            <select class="form-select" id="equipamento_id" name="equipamento_id" required <?= empty($equipamentosDisponiveis) ? 'disabled' : '' ?>>
                                <option value="">-- Selecione o equipamento disponível --</option>
                                <?php foreach ($equipamentosDisponiveis as $eq): ?>
                                    <option value="<?= $eq['id'] ?>">
                                        <?= htmlspecialchars($eq['nome']) ?> | Patrimônio: <?= htmlspecialchars($eq['patrimonio']) ?> <?= !empty($eq['categoria']) ? '[' . htmlspecialchars($eq['categoria']) . ']' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Apenas equipamentos com situação "DISPONÍVEL" são listados.</div>
                        </div>

                        <!-- Data e Hora do Empréstimo -->
                        <div class="col-md-6">
                            <label for="data_emprestimo" class="form-label">Data e Hora de Retirada <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control auto-now" id="data_emprestimo" name="data_emprestimo" required>
                        </div>

                        <!-- Data Prevista para Devolução -->
                        <div class="col-md-6">
                            <label for="data_prevista_devolucao" class="form-label">Previsão de Devolução <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control auto-tomorrow" id="data_prevista_devolucao" name="data_prevista_devolucao" required>
                            <div class="form-text">Não pode ser anterior à data de retirada.</div>
                        </div>

                        <!-- Observações -->
                        <div class="col-12">
                            <label for="observacoes" class="form-label">Observações e Estado Inicial do Item</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Informações de acessórios entregues (cabos, fontes), finalidade pedagógica etc."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/sce/public/emprestimos" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce" <?= (empty($equipamentosDisponiveis) || empty($usuariosAtivos)) ? 'disabled' : '' ?>>
                            <i class="bi bi-check-circle me-1"></i> Confirmar Empréstimo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
