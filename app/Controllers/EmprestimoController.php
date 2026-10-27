<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/EmprestimoController.php
 * DESCRIÇÃO: Controlador principal de Empréstimos e Devoluções de Equipamentos
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Models\Emprestimo;
use App\Models\Equipamento;
use App\Models\Usuario;
use Exception;

class EmprestimoController extends Controller
{
    /**
     * Listagem geral de empréstimos com múltiplos filtros e paginação
     */
    public function index(): void
    {
        $usuarioId     = !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
        $equipamentoId = !empty($_GET['equipamento_id']) ? (int)$_GET['equipamento_id'] : null;
        $status        = trim($_GET['status'] ?? '');
        $dataInicio    = trim($_GET['data_inicio'] ?? '');
        $dataFim       = trim($_GET['data_fim'] ?? '');
        $page          = (int)($_GET['pagina'] ?? 1);

        $resultado = Emprestimo::paginate(
            $usuarioId,
            $equipamentoId,
            $status,
            $dataInicio,
            $dataFim,
            $page,
            10
        );

        $usuarios = Usuario::getActiveList();

        $this->render('emprestimos/index', [
            'title'         => 'Gerenciamento de Empréstimos',
            'activeMenu'    => 'emprestimos',
            'emprestimos'   => $resultado['items'],
            'total'         => $resultado['total'],
            'pages'         => $resultado['pages'],
            'current'       => $resultado['current'],
            'usuarios'      => $usuarios,
            'usuarioId'     => $usuarioId,
            'equipamentoId' => $equipamentoId,
            'status'        => $status,
            'dataInicio'    => $dataInicio,
            'dataFim'       => $dataFim,
        ]);
    }

    /**
     * Exibe o formulário para registrar novo empréstimo
     */
    public function create(): void
    {
        $usuariosAtivos = Usuario::getActiveList();
        $equipamentosDisponiveis = Equipamento::getAvailableList();

        $this->render('emprestimos/create', [
            'title'                   => 'Registrar Novo Empréstimo',
            'activeMenu'              => 'emprestimos',
            'usuariosAtivos'          => $usuariosAtivos,
            'equipamentosDisponiveis' => $equipamentosDisponiveis,
        ]);
    }

    /**
     * Processa a gravação de um novo empréstimo em transação SQL
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/emprestimos/novo');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'usuario_id'              => 'required|integer',
            'equipamento_id'          => 'required|integer',
            'data_emprestimo'         => 'required',
            'data_prevista_devolucao' => 'required',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Preencha todos os campos obrigatórios.');
            $this->redirect('/emprestimos/novo');
        }

        $admin = Auth::user();

        try {
            // Formata datas para o MySQL
            $dataEmp = str_replace('T', ' ', $_POST['data_emprestimo']);
            if (strlen($dataEmp) === 16) {
                $dataEmp .= ':00';
            }

            $dataPrev = str_replace('T', ' ', $_POST['data_prevista_devolucao']);
            if (strlen($dataPrev) === 16) {
                $dataPrev .= ':00';
            }

            $emprestimoId = Emprestimo::registrarEmprestimo([
                'usuario_id'              => (int)$_POST['usuario_id'],
                'equipamento_id'          => (int)$_POST['equipamento_id'],
                'administrador_id'        => (int)$admin['id'],
                'data_emprestimo'         => $dataEmp,
                'data_prevista_devolucao' => $dataPrev,
                'observacoes'             => $_POST['observacoes'] ?? null,
            ]);

            $this->setFlashSuccess("Empréstimo #{$emprestimoId} registrado com sucesso! O equipamento agora está com a situação 'EMPRESTADO'.");
            $this->redirect('/emprestimos');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect('/emprestimos/novo');
        }
    }

    /**
     * Exibe tela de consulta/devolução de empréstimo
     */
    public function devolucao(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $emprestimo = null;

        if ($id > 0) {
            $emprestimo = Emprestimo::findById($id);
        }

        // Se nenhum ID específico foi passado, lista empréstimos em aberto para facilitar a devolução
        $abertos = Emprestimo::paginate(null, null, 'ABERTO', '', '', 1, 50)['items'];

        $this->render('emprestimos/devolucao', [
            'title'       => 'Registrar Devolução de Equipamento',
            'activeMenu'  => 'devolucoes',
            'emprestimo'  => $emprestimo,
            'abertos'     => $abertos,
        ]);
    }

    /**
     * Processa a devolução do equipamento em transação SQL
     */
    public function processarDevolucao(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/devolucoes');
        }

        $id = (int)($_POST['emprestimo_id'] ?? 0);
        $situacaoFinal = $_POST['situacao_final'] ?? 'DISPONIVEL';
        $observacoes = $_POST['observacoes_devolucao'] ?? '';
        $dataDevolucao = !empty($_POST['data_devolucao']) ? str_replace('T', ' ', $_POST['data_devolucao']) : date('Y-m-d H:i:s');
        if (strlen($dataDevolucao) === 16) {
            $dataDevolucao .= ':00';
        }

        if ($id <= 0) {
            $this->setFlashError('Nenhum empréstimo selecionado para devolução.');
            $this->redirect('/devolucoes');
        }

        try {
            Emprestimo::registrarDevolucao($id, $dataDevolucao, $observacoes, $situacaoFinal);

            $msg = "Devolução do empréstimo #{$id} concluída com sucesso!";
            if ($situacaoFinal === 'MANUTENCAO') {
                $msg .= " O equipamento foi encaminhado para a situação 'MANUTENÇÃO'.";
            } else {
                $msg .= " O equipamento já está novamente 'DISPONÍVEL' para novos empréstimos.";
            }

            $this->setFlashSuccess($msg);
            $this->redirect('/emprestimos');
        } catch (Exception $e) {
            $this->setFlashError($e->getMessage());
            $this->redirect("/devolucoes?id={$id}");
        }
    }
}
