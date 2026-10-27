/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: public/assets/js/app.js
 * DESCRIÇÃO: Scripts interativos do front-end com Sistema Unificado de Modal de Confirmação
 * para todos os CRUDs (Inclusão, Edição, Inativação/Exclusão de Equipamentos, Usuários, Empréstimos e Devoluções)
 * =====================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // 1. Controle da Sidebar em Dispositivos Móveis
    // -------------------------------------------------------------
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const appSidebar = document.querySelector('.app-sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggleBtn && appSidebar && sidebarOverlay) {
        sidebarToggleBtn.addEventListener('click', function () {
            appSidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', function () {
            appSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // -------------------------------------------------------------
    // 2. Sistema Unificado de Modal de Confirmação Bootstrap 5.3
    // -------------------------------------------------------------
    const modalEl = document.getElementById('modalConfirmacaoSce');
    let bsModal = null;
    if (modalEl && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalEl);
    }

    const modalTitulo = document.getElementById('modalConfirmacaoTitulo');
    const modalSubtitulo = document.getElementById('modalConfirmacaoSubtitulo');
    const modalMensagem = document.getElementById('modalConfirmacaoMensagem');
    const modalIcone = document.getElementById('modalConfirmacaoIcone');
    const btnConfirmarAcao = document.getElementById('btnModalConfirmarAcao');

    let formPendenteParaEnvio = null;

    /**
     * Abre o modal com estilos e textos configurados
     */
    function abrirModalConfirmacao(config, formAlvo) {
        if (!bsModal) {
            // Fallback para caso Bootstrap não carregue
            if (confirm(config.mensagem || 'Deseja confirmar esta ação?')) {
                formAlvo.submit();
            }
            return;
        }

        formPendenteParaEnvio = formAlvo;

        // Configuração de Título e Mensagem
        modalTitulo.textContent = config.titulo || 'Confirmação de Ação';
        modalSubtitulo.textContent = config.subtitulo || 'Sistema de Controle de Empréstimos';
        modalMensagem.innerHTML = config.mensagem || 'Deseja confirmar esta operação?';

        // Estilização do ícone e do botão de confirmação
        const tipo = config.tipo || 'info'; // 'primary', 'success', 'warning', 'danger'
        
        let iconeHtml = '<i class="bi bi-question-circle"></i>';
        let bgIcone = 'bg-primary-subtle text-primary';
        let btnClasse = 'btn-primary';

        switch (tipo) {
            case 'danger':
                iconeHtml = '<i class="bi bi-exclamation-triangle-fill"></i>';
                bgIcone = 'bg-danger-subtle text-danger';
                btnClasse = 'btn-danger';
                break;
            case 'warning':
                iconeHtml = '<i class="bi bi-exclamation-circle-fill"></i>';
                bgIcone = 'bg-warning-subtle text-warning';
                btnClasse = 'btn-warning text-dark';
                break;
            case 'success':
                iconeHtml = '<i class="bi bi-check-circle-fill"></i>';
                bgIcone = 'bg-success-subtle text-success';
                btnClasse = 'btn-success';
                break;
            case 'primary':
            default:
                iconeHtml = '<i class="bi bi-shield-check"></i>';
                bgIcone = 'bg-primary-subtle text-primary';
                btnClasse = 'btn-primary';
                break;
        }

        modalIcone.className = `d-flex align-items-center justify-content-center rounded-circle ${bgIcone}`;
        modalIcone.innerHTML = iconeHtml;

        btnConfirmarAcao.className = `btn ${btnClasse} px-4 fw-medium`;
        btnConfirmarAcao.innerHTML = `<i class="bi bi-check2 me-1"></i> ${config.textoBotao || 'Confirmar'}`;

        bsModal.show();
    }

    // Ação do botão confirmar dentro do modal
    if (btnConfirmarAcao) {
        btnConfirmarAcao.addEventListener('click', function () {
            if (formPendenteParaEnvio) {
                // Marca o form como já aprovado pelo modal para evitar loop
                formPendenteParaEnvio.dataset.modalConfirmado = 'true';
                if (bsModal) {
                    bsModal.hide();
                }
                formPendenteParaEnvio.submit();
            }
        });
    }

    // -------------------------------------------------------------
    // 3. Interceptação Automática de Todos os Formulários de CRUD
    // -------------------------------------------------------------
    document.querySelectorAll('form').forEach(form => {
        // Se for o formulário de logout, não intercepta com modal complexo
        if (form.getAttribute('action') && form.getAttribute('action').includes('/logout')) {
            return;
        }

        form.addEventListener('submit', function (e) {
            // Se já foi confirmado pelo modal, permite o envio normal
            if (this.dataset.modalConfirmado === 'true') {
                return true;
            }

            // Verifica validação nativa de HTML5 antes de exibir modal
            if (!this.checkValidity()) {
                return; // Deixa o navegador exibir os erros de campo obrigatório nativos
            }

            e.preventDefault();

            const action = (this.getAttribute('action') || '').toLowerCase();
            let config = {
                titulo: 'Confirmar Operação',
                subtitulo: 'Sistema de Controle de Empréstimos',
                mensagem: 'Deseja confirmar o envio dos dados?',
                tipo: 'primary',
                textoBotao: 'Confirmar'
            };

            // Detecta tipo de CRUD baseado na rota e atributos

            // A) EQUIPAMENTOS
            if (action.includes('/equipamentos/novo')) {
                const nome = this.querySelector('[name="nome"]')?.value || 'novo equipamento';
                config = {
                    titulo: 'Confirmar Inclusão de Equipamento',
                    subtitulo: 'Módulo de Equipamentos',
                    mensagem: `Deseja cadastrar o equipamento <strong>${escapeHtml(nome)}</strong> no inventário do SCE?`,
                    tipo: 'success',
                    textoBotao: 'Cadastrar Equipamento'
                };
            } else if (action.includes('/equipamentos/editar')) {
                const nome = this.querySelector('[name="nome"]')?.value || 'equipamento';
                config = {
                    titulo: 'Confirmar Alterações de Equipamento',
                    subtitulo: 'Módulo de Equipamentos',
                    mensagem: `Confirma a atualização dos dados cadastrais do equipamento <strong>${escapeHtml(nome)}</strong>?`,
                    tipo: 'primary',
                    textoBotao: 'Salvar Alterações'
                };
            } else if (action.includes('/equipamentos/inativar')) {
                config = {
                    titulo: 'Confirmar Inativação de Equipamento',
                    subtitulo: 'Módulo de Equipamentos',
                    mensagem: 'Tem certeza de que deseja <strong>inativar este equipamento</strong>? Ele ficará indisponível para novos empréstimos.',
                    tipo: 'danger',
                    textoBotao: 'Sim, Inativar'
                };
            }

            // B) USUÁRIOS
            else if (action.includes('/usuarios/novo')) {
                const nome = this.querySelector('[name="nome"]')?.value || 'novo usuário';
                config = {
                    titulo: 'Confirmar Cadastro de Usuário',
                    subtitulo: 'Módulo de Usuários',
                    mensagem: `Deseja registrar o usuário <strong>${escapeHtml(nome)}</strong> como solicitante ativo?`,
                    tipo: 'success',
                    textoBotao: 'Cadastrar Usuário'
                };
            } else if (action.includes('/usuarios/editar')) {
                const nome = this.querySelector('[name="nome"]')?.value || 'usuário';
                config = {
                    titulo: 'Confirmar Edição de Usuário',
                    subtitulo: 'Módulo de Usuários',
                    mensagem: `Confirma as alterações no cadastro de <strong>${escapeHtml(nome)}</strong>?`,
                    tipo: 'primary',
                    textoBotao: 'Atualizar Usuário'
                };
            } else if (action.includes('/usuarios/inativar')) {
                config = {
                    titulo: 'Confirmar Inativação de Usuário',
                    subtitulo: 'Módulo de Usuários',
                    mensagem: 'Tem certeza de que deseja <strong>inativar este usuário</strong>? Usuários inativos não podem solicitar empréstimos.',
                    tipo: 'danger',
                    textoBotao: 'Sim, Inativar'
                };
            } else if (action.includes('/usuarios/ativar')) {
                config = {
                    titulo: 'Confirmar Reativação de Usuário',
                    subtitulo: 'Módulo de Usuários',
                    mensagem: 'Deseja <strong>reativar este usuário</strong> para que possa realizar novos empréstimos?',
                    tipo: 'success',
                    textoBotao: 'Sim, Reativar'
                };
            }

            // C) EMPRÉSTIMOS
            else if (action.includes('/emprestimos/novo')) {
                const selEq = this.querySelector('#equipamento_id');
                const selUser = this.querySelector('#usuario_id');
                const eqTexto = selEq ? selEq.options[selEq.selectedIndex]?.text : 'Equipamento selecionado';
                const userTexto = selUser ? selUser.options[selUser.selectedIndex]?.text : 'Usuário selecionado';

                config = {
                    titulo: 'Confirmar Registro de Empréstimo',
                    subtitulo: 'Módulo de Empréstimos',
                    mensagem: `Confirma a saída do equipamento?<br><br>
                               <div class="p-2 bg-light rounded border small">
                                 <strong>Item:</strong> ${escapeHtml(eqTexto)}<br>
                                 <strong>Solicitante:</strong> ${escapeHtml(userTexto)}
                               </div><br>
                               O equipamento passará atomicamente para o status <strong>EMPRESTADO</strong>.`,
                    tipo: 'primary',
                    textoBotao: 'Confirmar Empréstimo'
                };
            }

            // D) DEVOLUÇÕES
            else if (action.includes('/devolucoes')) {
                const situacaoFinalEl = this.querySelector('#situacao_final');
                const situacaoFinal = situacaoFinalEl ? situacaoFinalEl.value : 'DISPONIVEL';
                const statusTexto = (situacaoFinal === 'MANUTENCAO') 
                    ? '<span class="badge bg-warning text-dark">MANUTENÇÃO</span>' 
                    : '<span class="badge bg-success">DISPONÍVEL</span>';

                config = {
                    titulo: 'Confirmar Devolução de Equipamento',
                    subtitulo: 'Módulo de Devoluções',
                    mensagem: `Confirma a formalização da devolução deste item?<br><br>
                               Situação final pós-recebimento: ${statusTexto}<br><br>
                               Esta operação encerrará o empréstimo em aberto e gravará o registro histórico.`,
                    tipo: 'success',
                    textoBotao: 'Confirmar Devolução'
                };
            }

            // E) MINHA CONTA / PERFIL
            else if (action.includes('/perfil')) {
                config = {
                    titulo: 'Confirmar Atualização de Perfil',
                    subtitulo: 'Minha Conta',
                    mensagem: 'Deseja salvar as alterações em seus dados de administrador?',
                    tipo: 'primary',
                    textoBotao: 'Salvar Alterações'
                };
            }

            abrirModalConfirmacao(config, this);
        });
    });

    /**
     * Escapa caracteres HTML para segurança em concatenações dinâmicas
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // -------------------------------------------------------------
    // 4. Preenchimento Automático de Datas
    // -------------------------------------------------------------
    const datetimeInputs = document.querySelectorAll('input[type="datetime-local"].auto-now');
    datetimeInputs.forEach(input => {
        if (!input.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });

    const dataPrevistaInput = document.querySelector('input[type="datetime-local"].auto-tomorrow');
    if (dataPrevistaInput && !dataPrevistaInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const year = tomorrow.getFullYear();
        const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const day = String(tomorrow.getDate()).padStart(2, '0');
        const hours = String(tomorrow.getHours()).padStart(2, '0');
        const minutes = String(tomorrow.getMinutes()).padStart(2, '0');
        dataPrevistaInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
});
