<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/auth/install.php
 * DESCRIÇÃO: Tela para criação do primeiro administrador do sistema
 * =====================================================================
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação Inicial — SCE</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/sce/public/assets/css/custom.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1E3A8A 0%, #1F2937 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem 1rem;
        }
        .install-card {
            width: 100%;
            max-width: 520px;
            background: #FFFFFF;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>

<div class="install-card">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-3 p-3 mb-3 shadow">
            <i class="bi bi-shield-lock-fill fs-2"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Configuração Inicial</h4>
        <p class="text-muted small">Nenhum administrador detectado. Cadastre o gestor inicial do sistema SCE.</p>
    </div>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="/sce/public/install" method="POST">
        <?= \App\Core\Csrf::input() ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Professor Carlos Eduardo" required autofocus>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail para Acesso</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="admin@instituicao.edu.br" required>
        </div>

        <div class="mb-4">
            <label for="senha" class="form-label">Senha de Acesso (mínimo 6 caracteres)</label>
            <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite uma senha forte" minlength="6" required>
        </div>

        <button type="submit" class="btn btn-success w-100 py-2 fs-6 shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Criar Primeiro Administrador
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
