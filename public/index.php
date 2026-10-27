<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: public/index.php
 * DESCRIÇÃO: Front Controller Único e Ponto de Entrada da Aplicação Web
 * =====================================================================
 */

declare(strict_types=1);

// Definição de fuso horário brasileiro padrão
date_default_timezone_set('America/Sao_Paulo');

// Tratamento e registro seguro de erros (não expõe detalhes internos na tela do usuário)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$storageLogs = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($storageLogs)) {
    @mkdir($storageLogs, 0777, true);
}
ini_set('error_log', $storageLogs . DIRECTORY_SEPARATOR . 'php_errors.log');

// Autoload PSR-4 simplificado para o namespace App\
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
        require $file;
    }
});

use App\Core\Router;
use App\Core\Auth;

// Inicia sessão com cookies seguros
Auth::startSession();

// Instanciação e mapeamento de rotas
$router = new Router();

// ==========================================
// ROTAS DE AUTENTICAÇÃO E INSTALAÇÃO INICIAL
// ==========================================
$router->get('/', 'AuthController', 'showLogin');
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->post('/logout', 'AuthController', 'logout');
$router->post('/install', 'AuthController', 'install');

// ==========================================
// ROTAS DO PAINEL E PERFIL (REQUER AUTENTICAÇÃO)
// ==========================================
$router->get('/dashboard', 'DashboardController', 'index', ['auth']);
$router->get('/perfil', 'AuthController', 'profile', ['auth']);
$router->post('/perfil', 'AuthController', 'updateProfile', ['auth']);

// ==========================================
// ROTAS DE EQUIPAMENTOS (CRUD COMPLETO)
// ==========================================
$router->get('/equipamentos', 'EquipamentoController', 'index', ['auth']);
$router->get('/equipamentos/novo', 'EquipamentoController', 'create', ['auth']);
$router->post('/equipamentos/novo', 'EquipamentoController', 'store', ['auth']);
$router->get('/equipamentos/detalhes', 'EquipamentoController', 'show', ['auth']);
$router->get('/equipamentos/editar', 'EquipamentoController', 'edit', ['auth']);
$router->post('/equipamentos/editar', 'EquipamentoController', 'update', ['auth']);
$router->post('/equipamentos/inativar', 'EquipamentoController', 'inactivate', ['auth']);

// ==========================================
// ROTAS DE USUÁRIOS (ALUNOS, PROFESSORES, ETC)
// ==========================================
$router->get('/usuarios', 'UsuarioController', 'index', ['auth']);
$router->get('/usuarios/novo', 'UsuarioController', 'create', ['auth']);
$router->post('/usuarios/novo', 'UsuarioController', 'store', ['auth']);
$router->get('/usuarios/editar', 'UsuarioController', 'edit', ['auth']);
$router->post('/usuarios/editar', 'UsuarioController', 'update', ['auth']);
$router->post('/usuarios/inativar', 'UsuarioController', 'inactivate', ['auth']);
$router->post('/usuarios/ativar', 'UsuarioController', 'activate', ['auth']);

// ==========================================
// ROTAS DE EMPRÉSTIMOS E DEVOLUÇÕES
// ==========================================
$router->get('/emprestimos', 'EmprestimoController', 'index', ['auth']);
$router->get('/emprestimos/novo', 'EmprestimoController', 'create', ['auth']);
$router->post('/emprestimos/novo', 'EmprestimoController', 'store', ['auth']);
$router->get('/devolucoes', 'EmprestimoController', 'devolucao', ['auth']);
$router->post('/devolucoes', 'EmprestimoController', 'processarDevolucao', ['auth']);

// ==========================================
// ROTAS DE RELATÓRIOS E EXPORTAÇÃO CSV
// ==========================================
$router->get('/relatorios', 'RelatorioController', 'index', ['auth']);
$router->get('/relatorios/exportar', 'RelatorioController', 'exportCsv', ['auth']);

// Despacha a requisição HTTP atual
$router->dispatch();
