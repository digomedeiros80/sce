<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: database/seed.php
 * DESCRIÇÃO: Script para inserção de dados fictícios e consistentes para testes e demonstração
 * EXECUÇÃO: php database/seed.php
 * =====================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

echo "======================================================" . PHP_EOL;
echo "  SCE — Script de Carga de Dados de Demonstração (Seed)  " . PHP_EOL;
echo "======================================================" . PHP_EOL;

try {
    $pdo = Database::getConnection();
} catch (\Exception $e) {
    echo "ERRO: Não foi possível conectar ao banco de dados: " . $e->getMessage() . PHP_EOL;
    echo "Certifique-se de que o MySQL do XAMPP está ativo e o banco 'sce_db' foi criado." . PHP_EOL;
    exit(1);
}

// 1. Cadastra Administrador de Teste caso não exista
$stmtAdmin = $pdo->prepare("SELECT id FROM administradores WHERE email = :email LIMIT 1");
$stmtAdmin->execute([':email' => 'admin@instituicao.edu.br']);
$adminId = $stmtAdmin->fetchColumn();

if (!$adminId) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insAdmin = $pdo->prepare("
        INSERT INTO administradores (nome, email, senha_hash, ativo, criado_em)
        VALUES ('Administrador Geral', 'admin@instituicao.edu.br', :senha, 1, NOW())
    ");
    $insAdmin->execute([':senha' => $hash]);
    $adminId = (int) $pdo->lastInsertId();
    echo "[✓] Administrador criado: admin@instituicao.edu.br (Senha: admin123)" . PHP_EOL;
} else {
    echo "[i] Administrador já existente (ID: $adminId)." . PHP_EOL;
}

// 2. Cadastra Cinco Usuários Fictícios
$usuarios = [
    [
        'nome'          => 'Mariana Souza Santos',
        'email'         => 'mariana.souza@aluno.instituicao.edu.br',
        'telefone'      => '(11) 98711-2233',
        'identificacao' => 'RA-2024101',
        'tipo'          => 'ALUNO',
    ],
    [
        'nome'          => 'Prof. Dr. Ricardo Oliveira',
        'email'         => 'ricardo.oliveira@instituicao.edu.br',
        'telefone'      => '(11) 97654-3210',
        'identificacao' => 'DOC-4089',
        'tipo'          => 'PROFESSOR',
    ],
    [
        'nome'          => 'Beatriz Helena Ramos',
        'email'         => 'beatriz.ramos@aluno.instituicao.edu.br',
        'telefone'      => '(11) 99122-3344',
        'identificacao' => 'RA-2023550',
        'tipo'          => 'ALUNO',
    ],
    [
        'nome'          => 'Carlos Eduardo Mendes',
        'email'         => 'carlos.mendes@instituicao.edu.br',
        'telefone'      => '(11) 98111-5566',
        'identificacao' => 'FUNC-1052',
        'tipo'          => 'FUNCIONARIO',
    ],
    [
        'nome'          => 'Profa. Juliana Martins Neves',
        'email'         => 'juliana.neves@instituicao.edu.br',
        'telefone'      => '(11) 98444-7788',
        'identificacao' => 'DOC-3120',
        'tipo'          => 'PROFESSOR',
    ],
];

$userIds = [];
$checkUser = $pdo->prepare("SELECT id FROM usuarios WHERE identificacao = :ident LIMIT 1");
$insUser = $pdo->prepare("
    INSERT INTO usuarios (nome, email, telefone, identificacao, tipo, ativo, criado_em)
    VALUES (:nome, :email, :telefone, :ident, :tipo, 1, NOW())
");

foreach ($usuarios as $u) {
    $checkUser->execute([':ident' => $u['identificacao']]);
    $id = $checkUser->fetchColumn();
    if (!$id) {
        $insUser->execute([
            ':nome'     => $u['nome'],
            ':email'    => $u['email'],
            ':telefone' => $u['telefone'],
            ':ident'    => $u['identificacao'],
            ':tipo'     => $u['tipo'],
        ]);
        $id = (int) $pdo->lastInsertId();
    }
    $userIds[] = $id;
}
echo "[✓] 5 Usuários verificados/inseridos com sucesso." . PHP_EOL;

// 3. Cadastra Dez Equipamentos Fictícios
$equipamentos = [
    [
        'nome'         => 'Notebook Dell Latitude 3420',
        'patrimonio'   => 'PAT-1001',
        'categoria'    => 'Notebook',
        'marca'        => 'Dell',
        'modelo'       => 'Latitude 3420 Core i5 16GB',
        'numero_serie' => 'SN-DL3420-9981',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Notebook Lenovo ThinkPad E14',
        'patrimonio'   => 'PAT-1002',
        'categoria'    => 'Notebook',
        'marca'        => 'Lenovo',
        'modelo'       => 'ThinkPad E14 Gen 4 Ryzen 7',
        'numero_serie' => 'SN-LN-THK-5521',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Projetor Epson PowerLite E20',
        'patrimonio'   => 'PAT-1003',
        'categoria'    => 'Projetor',
        'marca'        => 'Epson',
        'modelo'       => 'PowerLite E20 3400 Lumens',
        'numero_serie' => 'SN-EP-PL20-1102',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Projetor BenQ MX550',
        'patrimonio'   => 'PAT-1004',
        'categoria'    => 'Projetor',
        'marca'        => 'BenQ',
        'modelo'       => 'MX550 XGA 3600 Lumens',
        'numero_serie' => 'SN-BQ-MX55-4491',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Caixa de Som Portátil JBL PartyBox',
        'patrimonio'   => 'PAT-1005',
        'categoria'    => 'Áudio',
        'marca'        => 'JBL',
        'modelo'       => 'PartyBox Encore Essential',
        'numero_serie' => 'SN-JBL-PB100-3320',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Webcam Logitech C920s Pro Full HD',
        'patrimonio'   => 'PAT-1006',
        'categoria'    => 'Acessório',
        'marca'        => 'Logitech',
        'modelo'       => 'C920s Pro 1080p',
        'numero_serie' => 'SN-LOGI-C920-7711',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Tablet Samsung Galaxy Tab S6 Lite',
        'patrimonio'   => 'PAT-1007',
        'categoria'    => 'Tablet',
        'marca'        => 'Samsung',
        'modelo'       => 'Tab S6 Lite 64GB com Caneta S-Pen',
        'numero_serie' => 'SN-SM-TABS6-8801',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Microfone de Lapela Sem Fio Boya BY-M1',
        'patrimonio'   => 'PAT-1008',
        'categoria'    => 'Áudio',
        'marca'        => 'Boya',
        'modelo'       => 'BY-WM4 Pro Dual',
        'numero_serie' => 'SN-BY-WM4-6652',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Câmera Canon EOS Rebel T7',
        'patrimonio'   => 'PAT-1009',
        'categoria'    => 'Câmera',
        'marca'        => 'Canon',
        'modelo'       => 'EOS Rebel T7 com lente 18-55mm',
        'numero_serie' => 'SN-CAN-T7-2299',
        'status'       => 'DISPONIVEL',
    ],
    [
        'nome'         => 'Apresentador Passador de Slides Logitech R400',
        'patrimonio'   => 'PAT-1010',
        'categoria'    => 'Acessório',
        'marca'        => 'Logitech',
        'modelo'       => 'Wireless Presenter R400 Laser',
        'numero_serie' => 'SN-LOGI-R400-3310',
        'status'       => 'MANUTENCAO',
    ],
];

$eqIds = [];
$checkEq = $pdo->prepare("SELECT id, status FROM equipamentos WHERE patrimonio = :pat LIMIT 1");
$insEq = $pdo->prepare("
    INSERT INTO equipamentos (nome, patrimonio, categoria, marca, modelo, numero_serie, status, criado_em)
    VALUES (:nome, :pat, :cat, :marca, :modelo, :sn, :status, NOW())
");

foreach ($equipamentos as $eq) {
    $checkEq->execute([':pat' => $eq['patrimonio']]);
    $row = $checkEq->fetch();
    if (!$row) {
        $insEq->execute([
            ':nome'   => $eq['nome'],
            ':pat'    => $eq['patrimonio'],
            ':cat'    => $eq['categoria'],
            ':marca'  => $eq['marca'],
            ':modelo' => $eq['modelo'],
            ':sn'     => $eq['numero_serie'],
            ':status' => $eq['status'],
        ]);
        $id = (int) $pdo->lastInsertId();
    } else {
        $id = (int) $row['id'];
    }
    $eqIds[] = $id;
}
echo "[✓] 10 Equipamentos verificados/inseridos com sucesso." . PHP_EOL;

// 4. Cadastra Empréstimos de Demonstração (Devolvido, Aberto no prazo e Aberto em atraso)
// Empréstimo 1: Já devolvido
$checkEmp1 = $pdo->prepare("SELECT id FROM emprestimos WHERE equipamento_id = :eq AND usuario_id = :u AND status = 'DEVOLVIDO' LIMIT 1");
$checkEmp1->execute([':eq' => $eqIds[0], ':u' => $userIds[0]]);
if (!$checkEmp1->fetch()) {
    $dataEmp = date('Y-m-d H:i:s', strtotime('-10 days'));
    $dataPrev = date('Y-m-d H:i:s', strtotime('-7 days'));
    $dataDev = date('Y-m-d H:i:s', strtotime('-7 days 2 hours'));

    $pdo->prepare("
        INSERT INTO emprestimos (usuario_id, equipamento_id, administrador_id, data_emprestimo, data_prevista_devolucao, data_devolucao, observacoes, observacoes_devolucao, status)
        VALUES (:u, :eq, :adm, :demp, :dprev, :ddev, 'Para seminário de ADS', 'Equipamento devolvido íntegro', 'DEVOLVIDO')
    ")->execute([
        ':u' => $userIds[0],
        ':eq' => $eqIds[0],
        ':adm' => $adminId,
        ':demp' => $dataEmp,
        ':dprev' => $dataPrev,
        ':ddev' => $dataDev,
    ]);
    echo "[✓] Empréstimo histórico devolvido registrado." . PHP_EOL;
}

// Empréstimo 2: Em aberto no prazo (Equipamento 2 para Usuário 1)
$checkEmp2 = $pdo->prepare("SELECT id FROM emprestimos WHERE equipamento_id = :eq AND status = 'ABERTO' LIMIT 1");
$checkEmp2->execute([':eq' => $eqIds[1]]);
if (!$checkEmp2->fetch()) {
    $dataEmp = date('Y-m-d H:i:s', strtotime('-1 day'));
    $dataPrev = date('Y-m-d H:i:s', strtotime('+2 days'));

    $pdo->prepare("
        INSERT INTO emprestimos (usuario_id, equipamento_id, administrador_id, data_emprestimo, data_prevista_devolucao, observacoes, status)
        VALUES (:u, :eq, :adm, :demp, :dprev, 'Preparação de aula prática', 'ABERTO')
    ")->execute([
        ':u' => $userIds[1],
        ':eq' => $eqIds[1],
        ':adm' => $adminId,
        ':demp' => $dataEmp,
        ':dprev' => $dataPrev,
    ]);
    $pdo->prepare("UPDATE equipamentos SET status = 'EMPRESTADO' WHERE id = :id")->execute([':id' => $eqIds[1]]);
    echo "[✓] Empréstimo em aberto (no prazo) registrado para PAT-1002." . PHP_EOL;
}

// Empréstimo 3: Em atraso proposital (Equipamento 3 para Usuário 2)
$checkEmp3 = $pdo->prepare("SELECT id FROM emprestimos WHERE equipamento_id = :eq AND status = 'ABERTO' LIMIT 1");
$checkEmp3->execute([':eq' => $eqIds[2]]);
if (!$checkEmp3->fetch()) {
    $dataEmp = date('Y-m-d H:i:s', strtotime('-5 days'));
    $dataPrev = date('Y-m-d H:i:s', strtotime('-2 days')); // Prazo já vencido!

    $pdo->prepare("
        INSERT INTO emprestimos (usuario_id, equipamento_id, administrador_id, data_emprestimo, data_prevista_devolucao, observacoes, status)
        VALUES (:u, :eq, :adm, :demp, :dprev, 'Apresentação no auditório bloco B', 'ABERTO')
    ")->execute([
        ':u' => $userIds[2],
        ':eq' => $eqIds[2],
        ':adm' => $adminId,
        ':demp' => $dataEmp,
        ':dprev' => $dataPrev,
    ]);
    $pdo->prepare("UPDATE equipamentos SET status = 'EMPRESTADO' WHERE id = :id")->execute([':id' => $eqIds[2]]);
    echo "[✓] Empréstimo em ATRASO (vencido) registrado para PAT-1003 para validação dos alertas do Dashboard." . PHP_EOL;
}

echo "======================================================" . PHP_EOL;
echo "  Carga de dados de demonstração finalizada com êxito! " . PHP_EOL;
echo "======================================================" . PHP_EOL;
