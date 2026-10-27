<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/usuarios/index.php
 * DESCRIÇÃO: Listagem de Usuários (Alunos, Professores, Funcionários) com Filtros e Paginação
 * =====================================================================
 */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-1">Gerenciamento de Usuários</h5>
        <p class="text-muted small mb-0">Total de <?= number_format($total, 0, ',', '.') ?> usuário(s) cadastrado(s)</p>
    </div>
    <a href="/sce/public/usuarios/novo" class="btn btn-sce d-inline-flex align-items-center gap-1">
        <i class="bi bi-person-plus-fill"></i> Novo Usuário
    </a>
</div>

<!-- FILTROS -->
<div class="card card-saas mb-4">
    <div class="card-body p-3">
        <form action="/sce/public/usuarios" method="GET" class="row g-2 align-items-end">
            <div class="col-md-7">
                <label for="busca" class="form-label small">Buscar por Nome, Matrícula ou E-mail</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Digite nome, CPF/matrícula ou e-mail...">
                </div>
            </div>

            <div class="col-md-3">
                <label for="tipo" class="form-label small">Tipo de Solicitante</label>
                <select class="form-select form-select-sm" id="tipo" name="tipo">
                    <option value="">Todos os Tipos</option>
                    <option value="ALUNO" <?= ($tipo === 'ALUNO') ? 'selected' : '' ?>>Aluno</option>
                    <option value="PROFESSOR" <?= ($tipo === 'PROFESSOR') ? 'selected' : '' ?>>Professor</option>
                    <option value="FUNCIONARIO" <?= ($tipo === 'FUNCIONARIO') ? 'selected' : '' ?>>Funcionário</option>
                    <option value="OUTRO" <?= ($tipo === 'OUTRO') ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Filtrar">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="/sce/public/usuarios" class="btn btn-sm btn-light border" title="Limpar Filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TABELA DE USUÁRIOS -->
<div class="card card-saas">
    <div class="table-responsive">
        <table class="table table-modern table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nome Completo</th>
                    <th>Matrícula / Identificação</th>
                    <th>Tipo</th>
                    <th>Contato (E-mail / Telefone)</th>
                    <th>Situação</th>
                    <th class="text-end" style="width: 140px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-person-x fs-3 d-block mb-1"></i>
                            Nenhum usuário encontrado com os filtros informados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="text-muted">#<?= $u['id'] ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($u['nome']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['identificacao'] ?? '-') ?></span></td>
                            <td>
                                <?php
                                $badgeColor = match($u['tipo']) {
                                    'PROFESSOR' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'ALUNO' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                    'FUNCIONARIO' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                    default => 'bg-light text-dark border'
                                };
                                ?>
                                <span class="badge <?= $badgeColor ?>"><?= htmlspecialchars($u['tipo']) ?></span>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($u['email'] ?? 'Não informado') ?></div>
                                <?php if (!empty($u['telefone'])): ?>
                                    <div class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($u['telefone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$u['ativo'] === 1): ?>
                                    <span class="badge-status badge-disponivel"><i class="bi bi-check-circle"></i> Ativo</span>
                                <?php else: ?>
                                    <span class="badge-status badge-inativo"><i class="bi bi-slash-circle"></i> Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="/sce/public/usuarios/editar?id=<?= $u['id'] ?>" class="btn btn-sm btn-light border p-1 px-2" title="Editar Usuário">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ((int)$u['ativo'] === 1): ?>
                                        <form action="/sce/public/usuarios/inativar" method="POST" class="d-inline">
                                            <?= \App\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Inativar Usuário">
                                                <i class="bi bi-person-slash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="/sce/public/usuarios/ativar" method="POST" class="d-inline">
                                            <?= \App\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success p-1 px-2" title="Reativar Usuário">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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
            <nav aria-label="Navegação da tabela">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($current <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?busca=<?= urlencode($busca) ?>&tipo=<?= urlencode($tipo) ?>&pagina=<?= $current - 1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i === $current) ? 'active' : '' ?>">
                            <a class="page-link" href="?busca=<?= urlencode($busca) ?>&tipo=<?= urlencode($tipo) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($current >= $pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?busca=<?= urlencode($busca) ?>&tipo=<?= urlencode($tipo) ?>&pagina=<?= $current + 1 ?>">Próxima</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
