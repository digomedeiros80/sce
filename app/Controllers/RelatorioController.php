<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/RelatorioController.php
 * DESCRIÇÃO: Controlador para geração de relatórios dinâmicos, versão para impressão e exportação CSV segura
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Emprestimo;
use App\Models\Usuario;
use App\Models\Equipamento;

class RelatorioController extends Controller
{
    /**
     * Exibe o painel de relatórios com opções de filtros
     */
    public function index(): void
    {
        $tipo       = trim($_GET['tipo'] ?? 'todos');
        $usuarioId  = !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
        $dataInicio = trim($_GET['data_inicio'] ?? '');
        $dataFim    = trim($_GET['data_fim'] ?? '');

        $filtros = [
            'tipo'        => $tipo,
            'usuario_id'  => $usuarioId,
            'data_inicio' => $dataInicio,
            'data_fim'    => $dataFim,
        ];

        $dados = Emprestimo::getReportData($filtros);
        $usuarios = Usuario::getActiveList();

        $this->render('relatorios/index', [
            'title'      => 'Relatórios e Consultas Gerenciais',
            'activeMenu' => 'relatorios',
            'dados'      => $dados,
            'usuarios'   => $usuarios,
            'tipo'       => $tipo,
            'usuarioId'  => $usuarioId,
            'dataInicio' => $dataInicio,
            'dataFim'    => $dataFim,
        ]);
    }

    /**
     * Exporta os dados do relatório para formato CSV com sanitização contra CSV Injection (Formula Injection)
     */
    public function exportCsv(): void
    {
        $tipo       = trim($_GET['tipo'] ?? 'todos');
        $usuarioId  = !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
        $dataInicio = trim($_GET['data_inicio'] ?? '');
        $dataFim    = trim($_GET['data_fim'] ?? '');

        $filtros = [
            'tipo'        => $tipo,
            'usuario_id'  => $usuarioId,
            'data_inicio' => $dataInicio,
            'data_fim'    => $dataFim,
        ];

        $dados = Emprestimo::getReportData($filtros);

        $filename = 'relatorio_emprestimos_' . date('Ymd_His') . '.csv';

        // Cabeçalhos HTTP para download forçado de arquivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 para correta interpretação de acentos no Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        // Linha de cabeçalho
        fputcsv($output, [
            'Código',
            'Equipamento',
            'Patrimônio',
            'Categoria',
            'Usuário Solicitante',
            'Identificação / Matrícula',
            'Tipo de Usuário',
            'Data Empréstimo',
            'Previsão Devolução',
            'Data Devolução',
            'Situação',
            'Administrador Responsável',
            'Observações'
        ], ';');

        // Escrita das linhas com proteção contra CSV Formula Injection (=, +, -, @)
        foreach ($dados as $row) {
            fputcsv($output, [
                $this->sanitizeCsvField($row['id']),
                $this->sanitizeCsvField($row['equipamento_nome']),
                $this->sanitizeCsvField($row['equipamento_patrimonio']),
                $this->sanitizeCsvField($row['equipamento_categoria']),
                $this->sanitizeCsvField($row['usuario_nome']),
                $this->sanitizeCsvField($row['usuario_identificacao'] ?? '-'),
                $this->sanitizeCsvField($row['usuario_tipo']),
                $this->sanitizeCsvField(date('d/m/Y H:i', strtotime($row['data_emprestimo']))),
                $this->sanitizeCsvField(date('d/m/Y H:i', strtotime($row['data_prevista_devolucao']))),
                !empty($row['data_devolucao']) ? $this->sanitizeCsvField(date('d/m/Y H:i', strtotime($row['data_devolucao']))) : 'Pendente',
                $this->sanitizeCsvField($row['situacao_calculada']),
                $this->sanitizeCsvField($row['administrador_nome']),
                $this->sanitizeCsvField($row['observacoes'] ?? '-'),
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Sanitiza campos para evitar CSV Injection / Formula Injection em planilhas eletrônicas (Excel, Calc)
     *
     * @param mixed $value
     * @return string
     */
    private function sanitizeCsvField(mixed $value): string
    {
        $val = (string) $value;
        $firstChar = substr($val, 0, 1);
        // Se começar com caracteres interpretáveis como fórmula (=, +, -, @), prefixa com aspas simples segura
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $val;
        }
        return $val;
    }
}
