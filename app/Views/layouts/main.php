<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/layouts/main.php
 * DESCRIÇÃO: Layout Principal Administrativo com Sidebar, Topbar e Notificações Flash
 * =====================================================================
 */

$title = $title ?? 'SCE — Sistema de Controle de Empréstimos';
$activeMenu = $activeMenu ?? '';
$currentUser = $currentUser ?? ['nome' => 'Administrador', 'email' => ''];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — SCE</title>

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Estilos Customizados SCE -->
    <link href="/sce/public/assets/css/custom.css" rel="stylesheet">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-wrapper">
    <!-- SIDEBAR LATERAL -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="/sce/public/dashboard" class="sidebar-brand">
                <span class="brand-badge"><i class="bi bi-box-seam-fill"></i> SCE</span>
                <span class="brand-text">Controle</span>
            </a>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section-title">Menu Principal</div>

            <a href="/sce/public/dashboard" class="sidebar-link <?= ($activeMenu === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a href="/sce/public/equipamentos" class="sidebar-link <?= ($activeMenu === 'equipamentos') ? 'active' : '' ?>">
                <i class="bi bi-laptop"></i>
                <span>Equipamentos</span>
            </a>

            <a href="/sce/public/usuarios" class="sidebar-link <?= ($activeMenu === 'usuarios') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Usuários</span>
            </a>

            <div class="nav-section-title mt-3">Operações</div>

            <a href="/sce/public/emprestimos" class="sidebar-link <?= ($activeMenu === 'emprestimos') ? 'active' : '' ?>">
                <i class="bi bi-arrow-up-right-circle"></i>
                <span>Empréstimos</span>
            </a>

            <a href="/sce/public/devolucoes" class="sidebar-link <?= ($activeMenu === 'devolucoes') ? 'active' : '' ?>">
                <i class="bi bi-arrow-down-left-circle"></i>
                <span>Devoluções</span>
            </a>

            <div class="nav-section-title mt-3">Gerenciamento</div>

            <a href="/sce/public/relatorios" class="sidebar-link <?= ($activeMenu === 'relatorios') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Relatórios</span>
            </a>

            <a href="/sce/public/perfil" class="sidebar-link <?= ($activeMenu === 'perfil') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>
                <span>Minha Conta</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <form action="/sce/public/logout" method="POST" class="d-inline w-100">
                <?= \App\Core\Csrf::input() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                </button>
            </form>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="app-main">
        <!-- CABEÇALHO SUPERIOR (HEADER) -->
        <header class="app-header">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" aria-label="Alternar Menu Lateral">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h5 class="mb-0 fw-semibold text-dark"><?= htmlspecialchars($title) ?></h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($currentUser['nome'] ?? 'Administrador') ?></div>
                    <small class="text-muted" style="font-size: 0.78rem;">Gestor do Sistema</small>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px; background-color: #E0E7FF; color: #1E3A8A;">
                        <i class="bi bi-person-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><h6 class="dropdown-header">Olá, <?= htmlspecialchars($currentUser['nome'] ?? 'Admin') ?></h6></li>
                        <li><a class="dropdown-item" href="/sce/public/perfil"><i class="bi bi-person me-2"></i> Minha Conta</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="/sce/public/logout" method="POST">
                                <?= \App\Core\Csrf::input() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- CORPO DA PÁGINA COM FLASH ALERTS -->
        <main class="content-body">
            <!-- Cabeçalho exclusivo para impressão -->
            <div class="print-only-header">
                <h2>SCE — Sistema de Controle de Empréstimos de Equipamentos</h2>
                <p>Relatório Institucional emitido em: <?= date('d/m/Y H:i') ?></p>
                <hr>
            </div>

            <!-- MENSAGENS FLASH -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div><?= htmlspecialchars($flashSuccess) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div><?= htmlspecialchars($flashError) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashWarning)): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                    <div><?= htmlspecialchars($flashWarning) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <!-- CONTEÚDO INJETADO DA VIEW -->
            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<!-- MODAL GLOBAL DE CONFIRMAÇÃO DO SCE -->
<div class="modal fade" id="modalConfirmacaoSce" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 pb-0" id="modalConfirmacaoHeader">
                <div class="d-flex align-items-center gap-3">
                    <div id="modalConfirmacaoIcone" class="d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; font-size: 1.5rem;">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="modalConfirmacaoTitulo">Confirmação de Operação</h5>
                        <small class="text-muted" id="modalConfirmacaoSubtitulo">Sistema de Controle de Empréstimos</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body py-4 px-4 text-secondary" id="modalConfirmacaoMensagem" style="font-size: 0.98rem; line-height: 1.5;">
                Deseja realmente confirmar esta ação no sistema?
            </div>
            <div class="modal-footer bg-light border-top-0 px-4 py-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal" id="btnModalCancelar">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary px-4 fw-medium" id="btnModalConfirmarAcao">
                    <i class="bi bi-check2 me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 Bundle JS com Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts da Aplicação -->
<script src="/sce/public/assets/js/app.js"></script>

</body>
</html>
