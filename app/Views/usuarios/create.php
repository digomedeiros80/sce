<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/usuarios/create.php
 * DESCRIÇÃO: Formulário de Cadastro de Novo Usuário (Aluno, Professor, Funcionário)
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Novo Usuário Solicitante</h5>
            </div>

            <div class="card-body p-4">
                <form action="/sce/public/usuarios/novo" method="POST">
                    <?= \App\Core\Csrf::input() ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Lucas Gabriel da Silva" required autofocus>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo de Solicitante <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="ALUNO" selected>Aluno</option>
                                <option value="PROFESSOR">Professor</option>
                                <option value="FUNCIONARIO">Funcionário</option>
                                <option value="OUTRO">Outro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="identificacao" class="form-label">Identificação / Matrícula</label>
                            <input type="text" class="form-control" id="identificacao" name="identificacao" placeholder="Ex: 2026101988">
                        </div>

                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone / WhatsApp</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Ex: (11) 98765-4321">
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">E-mail Institucional ou Pessoal</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="usuario@instituicao.edu.br">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/sce/public/usuarios" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce">
                            <i class="bi bi-check-lg me-1"></i> Salvar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
