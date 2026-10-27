<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/auth/install_error.php
 * DESCRIÇÃO: Tela informativa amigável exibida caso o MySQL não esteja rodando
 * =====================================================================
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração do Banco de Dados — SCE</title>
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
            padding: 1.5rem;
        }
        .error-card {
            max-width: 650px;
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid #E5E7EB;
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="d-flex align-items-center gap-3 mb-3 text-warning">
        <i class="bi bi-database-exclamation fs-1"></i>
        <div>
            <h4 class="mb-0 text-dark fw-bold">Banco de Dados MySQL Não Conectado</h4>
            <small class="text-muted">A aplicação SCE precisa de conexão com o MySQL para operar.</small>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>Aviso:</strong> <?= htmlspecialchars($errorMessage ?? 'Não foi possível estabelecer conexão.') ?>
    </div>

    <h6 class="fw-bold mt-4">Como resolver no Windows com XAMPP:</h6>
    <ol class="small text-secondary ps-3">
        <li>Abra o <strong>XAMPP Control Panel</strong>.</li>
        <li>Clique no botão <strong>Start</strong> correspondente ao <strong>MySQL</strong>.</li>
        <li>Crie o banco de dados executando no terminal ou no phpMyAdmin:
            <pre class="bg-light p-2 rounded mt-1 border"><code>CREATE DATABASE sce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>
        </li>
        <li>Importe o arquivo estrutural localizado em:
            <code class="text-primary">c:\xampp\htdocs\sce\database\schema.sql</code>
        </li>
        <li>Recarregue esta página após iniciar o serviço.</li>
    </ol>

    <div class="mt-4 pt-3 border-top text-end">
        <a href="/sce/public/login" class="btn btn-primary btn-sce">
            <i class="bi bi-arrow-clockwise me-1"></i> Tentar Novamente
        </a>
    </div>
</div>

</body>
</html>
