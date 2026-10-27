<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/auth/login.php
 * DESCRIÇÃO: Tela de Login Administrativo com Design Moderno SaaS
 * =====================================================================
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — SCE Sistema de Controle de Empréstimos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 mb-3 shadow">
            <i class="bi bi-box-seam-fill fs-2"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Painel SCE</h4>
        <p class="text-muted small">Sistema de Controle de Empréstimos</p>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="/sce/public/login" method="POST">
        <?= \App\Core\Csrf::input() ?>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail Administrativo</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="admin@instituicao.edu.br" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="senha" class="form-label">Senha</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-sce w-100 py-2 fs-6 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Acessar Sistema
        </button>
    </form>

    <div class="text-center mt-4 pt-2 border-top">
        <small class="text-muted">Ambiente Local — XAMPP / ADS</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
