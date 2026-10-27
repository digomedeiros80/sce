<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/DashboardController.php
 * DESCRIÇÃO: Controlador do Painel Principal com métricas e últimos empréstimos
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Equipamento;
use App\Models\Emprestimo;
use App\Models\Usuario;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com indicadores e atividades recentes
     */
    public function index(): void
    {
        // Obtém métricas reais do banco de dados
        $metricsEquipamentos = Equipamento::getMetrics();
        $totalAtrasados      = Emprestimo::countAtrasados();
        $totalUsuariosAtivos = Usuario::countActive();

        // Obtém últimos empréstimos registrados
        $ultimosEmprestimos  = Emprestimo::getLatest(8);

        $this->render('dashboard/index', [
            'title'               => 'Painel de Controle — SCE',
            'activeMenu'          => 'dashboard',
            'metricsEquipamentos' => $metricsEquipamentos,
            'totalAtrasados'      => $totalAtrasados,
            'totalUsuariosAtivos' => $totalUsuariosAtivos,
            'ultimosEmprestimos'  => $ultimosEmprestimos,
        ]);
    }
}
