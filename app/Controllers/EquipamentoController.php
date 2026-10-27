<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/EquipamentoController.php
 * DESCRIÇÃO: Controlador para CRUD completo de Equipamentos com filtros e regras de negócio
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Equipamento;
use Exception;

class EquipamentoController extends Controller
{
    /**
     * Lista de equipamentos com busca e paginação
     */
    public function index(): void
    {
        $busca      = trim($_GET['busca'] ?? '');
        $patrimonio = trim($_GET['patrimonio'] ?? '');
        $categoria  = trim($_GET['categoria'] ?? '');
        $status     = trim($_GET['status'] ?? '');
        $page       = (int)($_GET['pagina'] ?? 1);

        $resultado = Equipamento::paginate($busca, $patrimonio, $categoria, $status, $page, 10);
        $categorias = Equipamento::getDistinctCategories();

        $this->render('equipamentos/index', [
            'title'      => 'Gerenciamento de Equipamentos',
            'activeMenu' => 'equipamentos',
            'equipamentos' => $resultado['items'],
            'total'      => $resultado['total'],
            'pages'      => $resultado['pages'],
            'current'    => $resultado['current'],
            'busca'      => $busca,
            'patrimonio' => $patrimonio,
            'categoria'  => $categoria,
            'status'     => $status,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Exibe formulário para novo equipamento
     */
    public function create(): void
    {
        $categorias = Equipamento::getDistinctCategories();

        $this->render('equipamentos/create', [
            'title'      => 'Novo Equipamento',
            'activeMenu' => 'equipamentos',
            'categorias' => $categorias,
        ]);
    }

    /**
     * Processa a inserção do novo equipamento
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/equipamentos/novo');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'nome'       => 'required|min:2|max:150',
            'patrimonio' => 'required|min:2|max:50',
            'categoria'  => 'max:100',
            'marca'      => 'max:100',
            'modelo'     => 'max:100',
            'numero_serie' => 'max:100',
            'status'     => 'in:DISPONIVEL,MANUTENCAO,INATIVO',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Verifique os dados informados.');
            $this->redirect('/equipamentos/novo');
        }

        try {
            $data = [
                'nome'         => trim($_POST['nome']),
                'descricao'    => trim($_POST['descricao'] ?? ''),
                'patrimonio'   => trim($_POST['patrimonio']),
                'categoria'    => trim($_POST['categoria'] ?? ''),
                'marca'        => trim($_POST['marca'] ?? ''),
                'modelo'       => trim($_POST['modelo'] ?? ''),
                'numero_serie' => trim($_POST['numero_serie'] ?? ''),
                'status'       => $_POST['status'] ?? 'DISPONIVEL',
            ];

            Equipamento::create($data);
            $this->setFlashSuccess("Equipamento '{$data['nome']}' cadastrado com sucesso!");
            $this->redirect('/equipamentos');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect('/equipamentos/novo');
        }
    }

    /**
     * Exibe os detalhes de um equipamento específico
     */
    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $equipamento = Equipamento::findById($id);

        if (!$equipamento) {
            $this->setFlashError('Equipamento não encontrado.');
            $this->redirect('/equipamentos');
        }

        $hasOpenLoan = Equipamento::hasOpenLoan($id);

        $this->render('equipamentos/show', [
            'title'       => "Equipamento: {$equipamento['nome']}",
            'activeMenu'  => 'equipamentos',
            'equipamento' => $equipamento,
            'hasOpenLoan' => $hasOpenLoan,
        ]);
    }

    /**
     * Exibe formulário de edição do equipamento
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $equipamento = Equipamento::findById($id);

        if (!$equipamento) {
            $this->setFlashError('Equipamento não encontrado.');
            $this->redirect('/equipamentos');
        }

        $categorias = Equipamento::getDistinctCategories();
        $hasOpenLoan = Equipamento::hasOpenLoan($id);

        $this->render('equipamentos/edit', [
            'title'       => "Editar: {$equipamento['nome']}",
            'activeMenu'  => 'equipamentos',
            'equipamento' => $equipamento,
            'categorias'  => $categorias,
            'hasOpenLoan' => $hasOpenLoan,
        ]);
    }

    /**
     * Processa a atualização dos dados do equipamento
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/equipamentos');
        }

        $id = (int)($_POST['id'] ?? 0);
        $equipamento = Equipamento::findById($id);

        if (!$equipamento) {
            $this->setFlashError('Equipamento não encontrado.');
            $this->redirect('/equipamentos');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'nome'       => 'required|min:2|max:150',
            'patrimonio' => 'required|min:2|max:50',
            'categoria'  => 'max:100',
            'marca'      => 'max:100',
            'modelo'     => 'max:100',
            'numero_serie' => 'max:100',
            'status'     => 'in:DISPONIVEL,EMPRESTADO,MANUTENCAO,INATIVO',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Dados inválidos.');
            $this->redirect("/equipamentos/editar?id={$id}");
        }

        try {
            $data = [
                'nome'         => trim($_POST['nome']),
                'descricao'    => trim($_POST['descricao'] ?? ''),
                'patrimonio'   => trim($_POST['patrimonio']),
                'categoria'    => trim($_POST['categoria'] ?? ''),
                'marca'        => trim($_POST['marca'] ?? ''),
                'modelo'       => trim($_POST['modelo'] ?? ''),
                'numero_serie' => trim($_POST['numero_serie'] ?? ''),
                'status'       => $_POST['status'] ?? $equipamento['status'],
            ];

            Equipamento::update($id, $data);
            $this->setFlashSuccess("Equipamento '{$data['nome']}' atualizado com sucesso!");
            $this->redirect('/equipamentos');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect("/equipamentos/editar?id={$id}");
        }
    }

    /**
     * Inativa um equipamento
     */
    public function inactivate(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/equipamentos');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            Equipamento::inactivate($id);
            $this->setFlashSuccess("Equipamento inativado com sucesso.");
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
        }

        $this->redirect('/equipamentos');
    }
}
