<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/equipamentos/create.php
 * DESCRIÇÃO: Formulário de Cadastro de Novo Equipamento
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-laptop me-2 text-primary"></i>Cadastrar Novo Equipamento</h5>
            </div>

            <div class="card-body p-4">
                <form action="/sce/public/equipamentos/novo" method="POST">
                    <?= \App\Core\Csrf::input() ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome do Equipamento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Notebook Dell Latitude 3420" required autofocus>
                        </div>

                        <div class="col-md-4">
                            <label for="patrimonio" class="form-label">Nº Patrimônio (Único) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="patrimonio" name="patrimonio" placeholder="Ex: PAT-0125" required>
                        </div>

                        <div class="col-md-4">
                            <label for="categoria" class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="categoria" name="categoria" list="lista-categorias" placeholder="Ex: Notebook, Projetor...">
                            <datalist id="lista-categorias">
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                <?php endforeach; ?>
                                <option value="Notebook">
                                <option value="Projetor">
                                <option value="Tablet">
                                <option value="Câmera Digital">
                                <option value="Adaptador de Vídeo">
                                <option value="Caixa de Som">
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label for="marca" class="form-label">Marca / Fabricante</label>
                            <input type="text" class="form-control" id="marca" name="marca" placeholder="Ex: Dell, Epson, Logitech">
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ex: Inspiron 15, PowerLite">
                        </div>

                        <div class="col-md-6">
                            <label for="numero_serie" class="form-label">Número de Série</label>
                            <input type="text" class="form-control" id="numero_serie" name="numero_serie" placeholder="Número de série de fábrica">
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Situação Inicial</label>
                            <select class="form-select" id="status" name="status">
                                <option value="DISPONIVEL" selected>Disponível (Pronto para Empréstimo)</option>
                                <option value="MANUTENCAO">Em Manutenção</option>
                                <option value="INATIVO">Inativo</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descricao" class="form-label">Especificações Técnicas / Observações</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Acessórios inclusos (carregador, bolsa, cabos), especificações de memória, processador etc."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/sce/public/equipamentos" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce">
                            <i class="bi bi-check-lg me-1"></i> Salvar Equipamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
