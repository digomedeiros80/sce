<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/404.php
 * DESCRIÇÃO: Página de Erro 404 Customizada e Amigável
 * =====================================================================
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página Não Encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/sce/public/assets/css/custom.css" rel="stylesheet">
    <style>
        body {
            background-color: #F3F4F6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-container {
            max-width: 500px;
            text-align: center;
            padding: 2.5rem;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #2563EB;
            line-height: 1;
        }
    </style>
</head>
<body>

<div class="error-container">
    <div class="error-code mb-2">404</div>
    <h4 class="fw-bold text-dark mb-2">Página Não Encontrada</h4>
    <p class="text-muted mb-4">A página que você está tentando acessar não existe ou foi movida.</p>
    <a href="/sce/public/dashboard" class="btn btn-sce px-4">
        <i class="bi bi-house-door me-1"></i> Voltar ao Painel
    </a>
</div>

</body>
</html>
