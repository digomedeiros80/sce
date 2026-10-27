<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: tests/run_tests.php
 * DESCRIÇÃO: Suíte de Testes Automatizados Executáveis Cobrindo os Cenários CT01 a CT10
 * EXECUÇÃO: php tests/run_tests.php
 * =====================================================================
 */

declare(strict_types=1);

// Carregamento de classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Database;
use App\Core\Auth;
use App\Core\Validator;
use App\Models\Administrador;
use App\Models\Usuario;
use App\Models\Equipamento;
use App\Models\Emprestimo;

echo "======================================================================" . PHP_EOL;
echo "        SUÍTE DE TESTES AUTOMATIZADOS DO SISTEMA SCE (CT01 a CT10)     " . PHP_EOL;
echo "======================================================================" . PHP_EOL;

// Configura banco de dados em memória SQLite compatível para execução isolada e sem sujar produção
$sqlite = new PDO('sqlite::memory:');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Criação do schema em memória para os testes
$sqlite->sqliteCreateFunction('NOW', function() {
    return date('Y-m-d H:i:s');
});

$sqlite->exec("
    CREATE TABLE administradores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha_hash TEXT NOT NULL,
        ativo INTEGER NOT NULL DEFAULT 1,
        criado_em TEXT,
        atualizado_em TEXT
    );

    CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT,
        telefone TEXT,
        identificacao TEXT,
        tipo TEXT NOT NULL DEFAULT 'ALUNO',
        ativo INTEGER NOT NULL DEFAULT 1,
        criado_em TEXT,
        atualizado_em TEXT
    );

    CREATE TABLE equipamentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        descricao TEXT,
        patrimonio TEXT NOT NULL UNIQUE,
        categoria TEXT,
        marca TEXT,
        modelo TEXT,
        numero_serie TEXT,
        status TEXT NOT NULL DEFAULT 'DISPONIVEL',
        criado_em TEXT,
        atualizado_em TEXT
    );

    CREATE TABLE emprestimos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        equipamento_id INTEGER NOT NULL,
        administrador_id INTEGER NOT NULL,
        data_emprestimo TEXT NOT NULL,
        data_prevista_devolucao TEXT NOT NULL,
        data_devolucao TEXT,
        observacoes TEXT,
        observacoes_devolucao TEXT,
        status TEXT NOT NULL DEFAULT 'ABERTO'
    );
");

// Injeta a conexão SQLite isolada no Singleton Database
Database::setConnection($sqlite);

$passedTests = 0;
$totalTests = 10;

function assertTest(bool $condition, string $id, string $description, ?string $detail = null): void {
    global $passedTests;
    if ($condition) {
        $passedTests++;
        echo " [APROVADO] {$id}: {$description}" . PHP_EOL;
        if ($detail) {
            echo "            Evidência: {$detail}" . PHP_EOL;
        }
    } else {
        echo " [REPROVADO] {$id}: {$description}" . PHP_EOL;
        if ($detail) {
            echo "            Falha: {$detail}" . PHP_EOL;
        }
    }
}

// ---------------------------------------------------------------------
// PREPARAÇÃO DE DADOS BÁSICOS
// ---------------------------------------------------------------------
$adminId = Administrador::create([
    'nome'  => 'Admin de Teste',
    'email' => 'admin.teste@instituicao.edu.br',
    'senha' => 'senhaSegura123',
]);

$usuarioAtivoId = Usuario::create([
    'nome'          => 'Aluno de Teste A',
    'email'         => 'aluno.a@teste.edu.br',
    'identificacao' => 'RA-9901',
    'tipo'          => 'ALUNO',
    'ativo'         => 1,
]);

// ---------------------------------------------------------------------
// CT01 — Cadastro de equipamento com dados válidos
// ---------------------------------------------------------------------
try {
    $eqId1 = Equipamento::create([
        'nome'         => 'Notebook Teste Dell',
        'patrimonio'   => 'PAT-TEST-01',
        'categoria'    => 'Notebook',
        'marca'        => 'Dell',
        'modelo'       => 'Vostro',
        'numero_serie' => 'SN-12345',
        'status'       => 'DISPONIVEL',
    ]);
    $eq1 = Equipamento::findById($eqId1);
    assertTest($eq1 !== null && $eq1['patrimonio'] === 'PAT-TEST-01', 'CT01', 'Cadastro de equipamento com dados válidos', "Equipamento ID {$eqId1} cadastrado com status '{$eq1['status']}'");
} catch (\Exception $e) {
    assertTest(false, 'CT01', 'Cadastro de equipamento com dados válidos', $e->getMessage());
}

// ---------------------------------------------------------------------
// CT02 — Tentativa de cadastro sem nome
// ---------------------------------------------------------------------
$val = new Validator();
$val->validate(['nome' => '', 'patrimonio' => 'PAT-SEM-NOME'], [
    'nome'       => 'required|min:2',
    'patrimonio' => 'required',
]);
$falhouNome = isset($val->getErrors()['nome']);
assertTest($falhouNome, 'CT02', 'Tentativa de cadastro sem nome', "Validador rejeitou campo obrigatório: " . ($val->getErrors()['nome'] ?? ''));

// ---------------------------------------------------------------------
// CT03 — Tentativa de cadastro com patrimônio duplicado
// ---------------------------------------------------------------------
try {
    Equipamento::create([
        'nome'       => 'Notebook Repetido',
        'patrimonio' => 'PAT-TEST-01', // Já cadastrado no CT01
        'status'     => 'DISPONIVEL',
    ]);
    assertTest(false, 'CT03', 'Tentativa de cadastro com patrimônio duplicado', 'Permitiu duplicidade indevida');
} catch (\Exception $e) {
    assertTest(true, 'CT03', 'Tentativa de cadastro com patrimônio duplicado', "Exceção disparada corretamente: " . $e->getMessage());
}

// ---------------------------------------------------------------------
// CT04 — Registro de empréstimo de equipamento disponível
// ---------------------------------------------------------------------
try {
    $dataEmp = date('Y-m-d H:i:s');
    $dataPrev = date('Y-m-d H:i:s', strtotime('+3 days'));

    $empId1 = Emprestimo::registrarEmprestimo([
        'usuario_id'              => $usuarioAtivoId,
        'equipamento_id'          => $eqId1,
        'administrador_id'        => $adminId,
        'data_emprestimo'         => $dataEmp,
        'data_prevista_devolucao' => $dataPrev,
        'observacoes'             => 'Empréstimo CT04',
    ]);

    $eqAtualizado = Equipamento::findById($eqId1);
    $statusMudouParaEmprestado = ($eqAtualizado['status'] === 'EMPRESTADO');
    assertTest($empId1 > 0 && $statusMudouParaEmprestado, 'CT04', 'Registro de empréstimo de equipamento disponível', "Empréstimo #{$empId1} registrado. Status do item atualizado para {$eqAtualizado['status']}");
} catch (\Exception $e) {
    assertTest(false, 'CT04', 'Registro de empréstimo de equipamento disponível', $e->getMessage());
}

// ---------------------------------------------------------------------
// CT05 — Tentativa de empréstimo de equipamento já emprestado
// ---------------------------------------------------------------------
try {
    Emprestimo::registrarEmprestimo([
        'usuario_id'              => $usuarioAtivoId,
        'equipamento_id'          => $eqId1, // já emprestado no CT04!
        'administrador_id'        => $adminId,
        'data_emprestimo'         => date('Y-m-d H:i:s'),
        'data_prevista_devolucao' => date('Y-m-d H:i:s', strtotime('+2 days')),
    ]);
    assertTest(false, 'CT05', 'Tentativa de empréstimo de equipamento já emprestado', 'Permitiu segundo empréstimo concorrente');
} catch (\Exception $e) {
    assertTest(true, 'CT05', 'Tentativa de empréstimo de equipamento já emprestado', "Transação bloqueou: " . $e->getMessage());
}

// ---------------------------------------------------------------------
// CT06 — Registro de devolução de empréstimo aberto
// ---------------------------------------------------------------------
try {
    $sucessoDev = Emprestimo::registrarDevolucao($empId1, date('Y-m-d H:i:s'), 'Devolvido 100% íntegro', 'DISPONIVEL');
    $empDevolvido = Emprestimo::findById($empId1);
    $eqVoltouDisponivel = Equipamento::findById($eqId1);

    $condicaoDevolucao = $sucessoDev && ($empDevolvido['status'] === 'DEVOLVIDO') && ($eqVoltouDisponivel['status'] === 'DISPONIVEL');
    assertTest($condicaoDevolucao, 'CT06', 'Registro de devolução de empréstimo aberto', "Empréstimo alterado para DEVOLVIDO e equipamento voltou para '{$eqVoltouDisponivel['status']}'");
} catch (\Exception $e) {
    assertTest(false, 'CT06', 'Registro de devolução de empréstimo aberto', $e->getMessage());
}

// ---------------------------------------------------------------------
// CT07 — Tentativa de devolução duplicada
// ---------------------------------------------------------------------
try {
    Emprestimo::registrarDevolucao($empId1, date('Y-m-d H:i:s'), 'Tentativa 2');
    assertTest(false, 'CT07', 'Tentativa de devolução duplicada', 'Permitiu devolução repetida');
} catch (\Exception $e) {
    assertTest(true, 'CT07', 'Tentativa de devolução duplicada', "Bloqueado com sucesso: " . $e->getMessage());
}

// ---------------------------------------------------------------------
// CT08 — Tentativa de acesso administrativo sem autenticação
// ---------------------------------------------------------------------
// Simula sessão vazia
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$_SESSION = [];
$estaLogadoSemSessao = Auth::check();
assertTest($estaLogadoSemSessao === false, 'CT08', 'Tentativa de acesso administrativo sem autenticação', "Auth::check() retornou false para requisições não autenticadas");

// ---------------------------------------------------------------------
// CT09 — Tentativa de inativação de usuário com empréstimo aberto
// ---------------------------------------------------------------------
try {
    // Registra novo empréstimo para o usuário
    $empId2 = Emprestimo::registrarEmprestimo([
        'usuario_id'              => $usuarioAtivoId,
        'equipamento_id'          => $eqId1,
        'administrador_id'        => $adminId,
        'data_emprestimo'         => date('Y-m-d H:i:s'),
        'data_prevista_devolucao' => date('Y-m-d H:i:s', strtotime('+1 day')),
    ]);

    // Tenta inativar usuário com empréstimo aberto
    Usuario::inactivate($usuarioAtivoId);
    assertTest(false, 'CT09', 'Tentativa de inativação de usuário com empréstimo aberto', 'Permitiu inativação indevida');
} catch (\Exception $e) {
    assertTest(true, 'CT09', 'Tentativa de inativação de usuário com empréstimo aberto', "Inativação impedida: " . $e->getMessage());
}

// ---------------------------------------------------------------------
// CT10 — Consulta de empréstimos atrasados
// ---------------------------------------------------------------------
try {
    // Cadastra um segundo equipamento para criar propositalmente um empréstimo vencido
    $eqId2 = Equipamento::create([
        'nome'       => 'Projetor Atraso CT10',
        'patrimonio' => 'PAT-ATRASO-10',
        'status'     => 'DISPONIVEL',
    ]);

    // Insere empréstimo com data prevista no passado
    $sqlite->prepare("
        INSERT INTO emprestimos (usuario_id, equipamento_id, administrador_id, data_emprestimo, data_prevista_devolucao, status)
        VALUES (:u, :eq, :adm, :demp, :dprev, 'ABERTO')
    ")->execute([
        ':u'     => $usuarioAtivoId,
        ':eq'    => $eqId2,
        ':adm'   => $adminId,
        ':demp'  => date('Y-m-d H:i:s', strtotime('-5 days')),
        ':dprev' => date('Y-m-d H:i:s', strtotime('-2 days')), // vencido há 2 dias
    ]);

    $totalAtrasados = Emprestimo::countAtrasados();
    assertTest($totalAtrasados >= 1, 'CT10', 'Consulta de empréstimos atrasados', "Identificou {$totalAtrasados} empréstimo(s) em atraso com data prevista expirada");
} catch (\Exception $e) {
    assertTest(false, 'CT10', 'Consulta de empréstimos atrasados', $e->getMessage());
}

echo "======================================================================" . PHP_EOL;
echo "RESULTADO FINAL: {$passedTests} de {$totalTests} testes APROVADOS com sucesso!" . PHP_EOL;
echo "======================================================================" . PHP_EOL;

if ($passedTests === $totalTests) {
    exit(0);
} else {
    exit(1);
}
