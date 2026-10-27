<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Views/auth/profile.php
 * DESCRIÇÃO: Página Minha Conta do Administrador
 * =====================================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas">
            <div class="card-header-clean">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Meus Dados de Administrador</h5>
            </div>
            <div class="card-body p-4">
                <form action="/sce/public/perfil" method="POST">
                    <?= \App\Core\Csrf::input() ?>

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail de Login</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-key me-1"></i>Alterar Senha de Acesso (Opcional)</h6>
                    <div class="mb-3">
                        <label for="nova_senha" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" placeholder="Deixe em branco para manter a senha atual">
                        <div class="form-text">Mínimo de 6 caracteres. Deixe em branco caso não queira alterar.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/sce/public/dashboard" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-sce">
                            <i class="bi bi-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
