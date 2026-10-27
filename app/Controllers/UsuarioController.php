<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/UsuarioController.php
 * DESCRIÇÃO: Controlador para gerenciamento de Usuários solicitantes
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;
use Exception;

class UsuarioController extends Controller
{
    /**
     * Lista de usuários com filtros e paginação
     */
    public function index(): void
    {
        $busca = trim($_GET['busca'] ?? '');
        $tipo  = trim($_GET['tipo'] ?? '');
        $page  = (int)($_GET['pagina'] ?? 1);

        $resultado = Usuario::paginate($busca, $tipo, $page, 10);

        $this->render('usuarios/index', [
            'title'      => 'Gerenciamento de Usuários',
            'activeMenu' => 'usuarios',
            'usuarios'   => $resultado['items'],
            'total'      => $resultado['total'],
            'pages'      => $resultado['pages'],
            'current'    => $resultado['current'],
            'busca'      => $busca,
            'tipo'       => $tipo,
        ]);
    }

    /**
     * Exibe formulário para cadastrar novo usuário
     */
    public function create(): void
    {
        $this->render('usuarios/create', [
            'title'      => 'Novo Usuário',
            'activeMenu' => 'usuarios',
        ]);
    }

    /**
     * Processa a inserção do usuário
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/usuarios/novo');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'nome'          => 'required|min:3|max:150',
            'email'         => 'email|max:150',
            'telefone'      => 'max:20',
            'identificacao' => 'max:50',
            'tipo'          => 'required|in:ALUNO,PROFESSOR,FUNCIONARIO,OUTRO',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Verifique os dados informados.');
            $this->redirect('/usuarios/novo');
        }

        try {
            $data = [
                'nome'          => trim($_POST['nome']),
                'email'         => trim($_POST['email'] ?? ''),
                'telefone'      => trim($_POST['telefone'] ?? ''),
                'identificacao' => trim($_POST['identificacao'] ?? ''),
                'tipo'          => $_POST['tipo'],
                'ativo'         => 1,
            ];

            Usuario::create($data);
            $this->setFlashSuccess("Usuário '{$data['nome']}' cadastrado com sucesso!");
            $this->redirect('/usuarios');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect('/usuarios/novo');
        }
    }

    /**
     * Exibe formulário de edição do usuário
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $usuario = Usuario::findById($id);

        if (!$usuario) {
            $this->setFlashError('Usuário não encontrado.');
            $this->redirect('/usuarios');
        }

        $hasOpenLoans = Usuario::hasOpenLoans($id);

        $this->render('usuarios/edit', [
            'title'        => "Editar: {$usuario['nome']}",
            'activeMenu'   => 'usuarios',
            'usuario'      => $usuario,
            'hasOpenLoans' => $hasOpenLoans,
        ]);
    }

    /**
     * Processa a atualização dos dados do usuário
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/usuarios');
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuario = Usuario::findById($id);

        if (!$usuario) {
            $this->setFlashError('Usuário não encontrado.');
            $this->redirect('/usuarios');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'nome'          => 'required|min:3|max:150',
            'email'         => 'email|max:150',
            'telefone'      => 'max:20',
            'identificacao' => 'max:50',
            'tipo'          => 'required|in:ALUNO,PROFESSOR,FUNCIONARIO,OUTRO',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Dados inválidos.');
            $this->redirect("/usuarios/editar?id={$id}");
        }

        $ativo = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;

        // Regra de segurança: se estiver tentando inativar pelo formulário de edição, checa empréstimos
        if ($ativo === 0 && (int)$usuario['ativo'] === 1) {
            if (Usuario::hasOpenLoans($id)) {
                $this->setFlashError('Não é permitido inativar usuário que possui empréstimos em aberto.');
                $this->redirect("/usuarios/editar?id={$id}");
            }
        }

        try {
            $data = [
                'nome'          => trim($_POST['nome']),
                'email'         => trim($_POST['email'] ?? ''),
                'telefone'      => trim($_POST['telefone'] ?? ''),
                'identificacao' => trim($_POST['identificacao'] ?? ''),
                'tipo'          => $_POST['tipo'],
                'ativo'         => $ativo,
            ];

            Usuario::update($id, $data);
            $this->setFlashSuccess("Usuário '{$data['nome']}' atualizado com sucesso!");
            $this->redirect('/usuarios');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect("/usuarios/editar?id={$id}");
        }
    }

    /**
     * Inativa um usuário garantindo a ausência de empréstimos em aberto
     */
    public function inactivate(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/usuarios');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            Usuario::inactivate($id);
            $this->setFlashSuccess("Usuário inativado com sucesso.");
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
        }

        $this->redirect('/usuarios');
    }

    /**
     * Reativa um usuário
     */
    public function activate(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/usuarios');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            Usuario::activate($id);
            $this->setFlashSuccess("Usuário reativado com sucesso.");
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
        }

        $this->redirect('/usuarios');
    }
}
