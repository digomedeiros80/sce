<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/equipamentos/index.php
 * DESCRIÇÃO: Listagem de Equipamentos com Busca, Filtros por Categoria/Status e Paginação
 * =====================================================================
 */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-1">Inventário de Equipamentos</h5>
        <p class="text-muted small mb-0">Total de <?= number_format($total, 0, ',', '.') ?> equipamento(s) cadastrado(s)</p>
    </div>
    <a href="/sce/public/equipamentos/novo" class="btn btn-sce d-inline-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i> Novo Equipamento
    </a>
</div>

<!-- FILTROS DE PESQUISA -->
<div class="card card-saas mb-4">
    <div class="card-body p-3">
        <form action="/sce/public/equipamentos" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="busca" class="form-label small">Busca Geral</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome, modelo ou fabricante...">
                </div>
            </div>

            <div class="col-md-2">
                <label for="patrimonio" class="form-label small">Patrimônio</label>
                <input type="text" class="form-control form-control-sm" id="patrimonio" name="patrimonio" value="<?= htmlspecialchars($patrimonio) ?>" placeholder="Ex: PAT-001">
            </div>

            <div class="col-md-3">
                <label for="categoria" class="form-label small">Categoria</label>
                <select class="form-select form-select-sm" id="categoria" name="categoria">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($categoria === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label small">Situação</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">Todas</option>
                    <option value="DISPONIVEL" <?= ($status === 'DISPONIVEL') ? 'selected' : '' ?>>Disponível</option>
                    <option value="EMPRESTADO" <?= ($status === 'EMPRESTADO') ? 'selected' : '' ?>>Emprestado</option>
                    <option value="MANUTENCAO" <?= ($status === 'MANUTENCAO') ? 'selected' : '' ?>>Manutenção</option>
                    <option value="INATIVO" <?= ($status === 'INATIVO') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Filtrar">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="/sce/public/equipamentos" class="btn btn-sm btn-light border" title="Limpar Filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TABELA DE RESULTADOS -->
<div class="card card-saas">
    <div class="table-responsive">
        <table class="table table-modern table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nome do Equipamento</th>
                    <th>Patrimônio</th>
                    <th>Categoria</th>
                    <th>Marca / Modelo</th>
                    <th>Situação</th>
                    <th class="text-end" style="width: 140px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipamentos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-1"></i>
                            Nenhum equipamento encontrado com os filtros aplicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipamentos as $eq): ?>
                        <tr>
                            <td class="text-muted">#<?= $eq['id'] ?></td>
                            <td class="fw-medium text-dark">
                                <a href="/sce/public/equipamentos/detalhes?id=<?= $eq['id'] ?>" class="text-decoration-none text-dark fw-semibold">
                                    <?= htmlspecialchars($eq['nome']) ?>
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($eq['patrimonio']) ?></span></td>
                            <td><?= htmlspecialchars($eq['categoria'] ?? '-') ?></td>
                            <td class="small text-muted">
                                <?= htmlspecialchars(trim(($eq['marca'] ?? '') . ' ' . ($eq['modelo'] ?? '')) ?: '-') ?>
                            </td>
                            <td>
                                <?php if ($eq['status'] === 'DISPONIVEL'): ?>
                                    <span class="badge-status badge-disponivel"><i class="bi bi-check-circle"></i> Disponível</span>
                                <?php elseif ($eq['status'] === 'EMPRESTADO'): ?>
                                    <span class="badge-status badge-emprestado"><i class="bi bi-arrow-up-right"></i> Emprestado</span>
                                <?php elseif ($eq['status'] === 'MANUTENCAO'): ?>
                                    <span class="badge-status badge-manutencao"><i class="bi bi-tools"></i> Manutenção</span>
                                <?php else: ?>
                                    <span class="badge-status badge-inativo"><i class="bi bi-slash-circle"></i> Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="/sce/public/equipamentos/detalhes?id=<?= $eq['id'] ?>" class="btn btn-sm btn-light border p-1 px-2" title="Visualizar Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/sce/public/equipamentos/editar?id=<?= $eq['id'] ?>" class="btn btn-sm btn-light border p-1 px-2" title="Editar Equipamento">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($eq['status'] !== 'INATIVO' && $eq['status'] !== 'EMPRESTADO'): ?>
                                        <form action="/sce/public/equipamentos/inativar" method="POST" class="d-inline">
                                            <?= \App\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $eq['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Inativar Equipamento">
                                                <i class="bi bi-trash"></i>
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
                        <a class="page-link" href="?busca=<?= urlencode($busca) ?>&patrimonio=<?= urlencode($patrimonio) ?>&categoria=<?= urlencode($categoria) ?>&status=<?= urlencode($status) ?>&pagina=<?= $current - 1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i === $current) ? 'active' : '' ?>">
                            <a class="page-link" href="?busca=<?= urlencode($busca) ?>&patrimonio=<?= urlencode($patrimonio) ?>&categoria=<?= urlencode($categoria) ?>&status=<?= urlencode($status) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($current >= $pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?busca=<?= urlencode($busca) ?>&patrimonio=<?= urlencode($patrimonio) ?>&categoria=<?= urlencode($categoria) ?>&status=<?= urlencode($status) ?>&pagina=<?= $current + 1 ?>">Próxima</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
