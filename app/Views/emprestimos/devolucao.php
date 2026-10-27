<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/emprestimos/devolucao.php
 * DESCRIÇÃO: Interface Operacional de Devoluções de Equipamentos com Opção de Envio para Manutenção
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-box-arrow-in-down-left me-2 text-success"></i>Registrar Devolução de Equipamento</h5>
            </div>

            <div class="card-body p-4">
                <?php if ($emprestimo): ?>
                    <!-- DEVOLUÇÃO DE UM EMPRÉSTIMO ESPECÍFICO SELECIONADO -->
                    <div class="card bg-light border p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-primary me-2">Empréstimo #<?= $emprestimo['id'] ?></span>
                                <h5 class="d-inline fw-bold text-dark"><?= htmlspecialchars($emprestimo['equipamento_nome']) ?></h5>
                            </div>
                            <?php if ($emprestimo['atrasado'] == 1): ?>
                                <span class="badge-status badge-atrasado"><i class="bi bi-exclamation-triangle-fill"></i> Devolução com Atraso</span>
                            <?php else: ?>
                                <span class="badge-status badge-emprestado"><i class="bi bi-clock"></i> No Prazo</span>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2 small text-secondary mt-1">
                            <div class="col-sm-6">
                                <strong>Patrimônio:</strong> <?= htmlspecialchars($emprestimo['equipamento_patrimonio']) ?>
                            </div>
                            <div class="col-sm-6">
                                <strong>Usuário Solicitante:</strong> <?= htmlspecialchars($emprestimo['usuario_nome']) ?> (<?= htmlspecialchars($emprestimo['usuario_tipo']) ?>)
                            </div>
                            <div class="col-sm-6">
                                <strong>Data de Retirada:</strong> <?= date('d/m/Y H:i', strtotime($emprestimo['data_emprestimo'])) ?>
                            </div>
                            <div class="col-sm-6">
                                <strong>Data Prevista:</strong> <?= date('d/m/Y H:i', strtotime($emprestimo['data_prevista_devolucao'])) ?>
                            </div>
                            <?php if (!empty($emprestimo['observacoes'])): ?>
                                <div class="col-12 mt-2">
                                    <strong>Obs. na saída:</strong> <em><?= htmlspecialchars($emprestimo['observacoes']) ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($emprestimo['status'] === 'DEVOLVIDO'): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-check2-circle me-1"></i> Este empréstimo já foi encerrado e devolvido em <?= date('d/m/Y H:i', strtotime($emprestimo['data_devolucao'])) ?>.
                        </div>
                        <a href="/sce/public/emprestimos" class="btn btn-light border">Voltar à Lista de Empréstimos</a>
                    <?php else: ?>
                        <!-- FORMULÁRIO DE CONFIRMAÇÃO DE DEVOLUÇÃO -->
                        <form action="/sce/public/devolucoes" method="POST">
                            <?= \App\Core\Csrf::input() ?>
                            <input type="hidden" name="emprestimo_id" value="<?= $emprestimo['id'] ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="data_devolucao" class="form-label">Data e Hora da Devolução Efetiva <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control auto-now" id="data_devolucao" name="data_devolucao" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="situacao_final" class="form-label">Situação do Equipamento após a Devolução <span class="text-danger">*</span></label>
                                    <select class="form-select" id="situacao_final" name="situacao_final" required>
                                        <option value="DISPONIVEL" selected>Disponível (Em perfeito estado para novos empréstimos)</option>
                                        <option value="MANUTENCAO">Manutenção (Apresenta defeito, avaria ou necessita reparo)</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="observacoes_devolucao" class="form-label">Observações sobre a Devolução / Estado do Equipamento</label>
                                    <textarea class="form-control" id="observacoes_devolucao" name="observacoes_devolucao" rows="3" placeholder="Ex: Equipamento entregue completo com carregador sem avarias. Ou: Teclado com tecla solta encaminhado para reparo."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="/sce/public/emprestimos" class="btn btn-light border">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle me-1"></i> Confirmar Devolução
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- NENHUM EMPRÉSTIMO SELECIONADO: LISTA PARA ESCOLHA -->
                    <p class="text-muted">Selecione um dos empréstimos em aberto abaixo para registrar o recebimento:</p>

                    <?php if (empty($abertos)): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                            <div>Não há nenhum equipamento com empréstimo em aberto pendente de devolução!</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-modern table-hover">
                                <thead>
                                    <tr>
                                        <th>Cód.</th>
                                        <th>Equipamento</th>
                                        <th>Patrimônio</th>
                                        <th>Usuário</th>
                                        <th>Previsão</th>
                                        <th>Situação</th>
                                        <th class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($abertos as $aberto): ?>
                                        <tr class="<?= ($aberto['atrasado'] == 1) ? 'table-danger' : '' ?>">
                                            <td>#<?= $aberto['id'] ?></td>
                                            <td class="fw-medium text-dark"><?= htmlspecialchars($aberto['equipamento_nome']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($aberto['equipamento_patrimonio']) ?></span></td>
                                            <td><?= htmlspecialchars($aberto['usuario_nome']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($aberto['data_prevista_devolucao'])) ?></td>
                                            <td>
                                                <?php if ($aberto['atrasado'] == 1): ?>
                                                    <span class="badge-status badge-atrasado"><i class="bi bi-alarm"></i> Vencido</span>
                                                <?php else: ?>
                                                    <span class="badge-status badge-emprestado">Em Aberto</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="/sce/public/devolucoes?id=<?= $aberto['id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-box-arrow-in-down-left me-1"></i> Devolver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
