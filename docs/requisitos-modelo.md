# SCE — Sistema de Controle de Empréstimos de Equipamentos
## Documento 2: Referência para Requisitos (Inventário Funcional)

Este documento apresenta o inventário técnico de todas as funcionalidades implementadas no sistema, detalhando ações disponíveis, campos obrigatórios, regras de validação e comportamentos esperados para apoiar a elaboração de trabalhos acadêmicos de ADS.

---

### Módulo 1: Autenticação e Gestão de Acesso
- **RF01.1 — Instalação Inicial:**
  - *Comportamento:* Quando a base de dados não contém administradores cadastrados, a rota `/login` exibe automaticamente a interface de cadastro do primeiro administrador.
  - *Campos obrigatórios:* Nome (mín. 3 letras), E-mail institucional (válido), Senha (mín. 6 caracteres).
  - *Segurança:* Hash gerado via `password_hash($senha, PASSWORD_DEFAULT)`.
- **RF01.2 — Autenticação Administrativa (Login):**
  - *Campos obrigatórios:* E-mail e Senha.
  - *Regra:* Verifica `ativo = 1` e validação com `password_verify()`. Regeneração de ID de sessão com `session_regenerate_id(true)`.
- **RF01.3 — Encerramento Seguro (Logout):**
  - *Comportamento:* Limpa a superglobal `$_SESSION`, destrói a sessão e invalida o cookie de sessão do navegador.
- **RF01.4 — Edição de Perfil (Minha Conta):**
  - *Campos obrigatórios:* Nome, E-mail.
  - *Campo opcional:* Nova Senha (mín. 6 caracteres caso preenchida).

---

### Módulo 2: Painel de Controle (Dashboard)
- **RF02.1 — Métricas em Tempo Real:**
  - Total de Equipamentos cadastrados.
  - Total de Equipamentos Disponíveis (`status = 'DISPONIVEL'`).
  - Total de Equipamentos Emprestados (`status = 'EMPRESTADO'`).
  - Total de Equipamentos em Manutenção (`status = 'MANUTENCAO'`).
  - Total de Empréstimos em Atraso (`status = 'ABERTO' AND data_prevista_devolucao < NOW()`).
  - Total de Usuários Ativos (`ativo = 1`).
- **RF02.2 — Tabela de Últimos Empréstimos:**
  - Apresenta os últimos empréstimos cadastrados com indicador visual de status e link direto para devolução rápida.

---

### Módulo 3: Equipamentos
- **RF03.1 — Cadastro de Equipamento:**
  - *Campos obrigatórios:* Nome (mín. 2 caracteres) e Patrimônio (único no sistema).
  - *Campos opcionais:* Categoria, Marca, Modelo, Número de Série, Especificações Técnicas/Observações.
  - *Validações:* Unicidade de patrimônio validada em banco de dados antes da inserção.
- **RF03.2 — Listagem e Pesquisa:**
  - Filtro por termo geral (nome, marca, modelo).
  - Filtro por patrimônio exato/parcial.
  - Filtro por categoria (dropdown dinâmico).
  - Filtro por situação (`DISPONIVEL`, `EMPRESTADO`, `MANUTENCAO`, `INATIVO`).
  - Paginação com 10 registros por página.
- **RF03.3 — Edição com Restrição de Consistência:**
  - *Regra de Negócio:* Não permite alterar a situação manualmente para `DISPONIVEL` ou `INATIVO` enquanto o equipamento estiver com empréstimo ativo em aberto.
- **RF03.4 — Inativação Segura:**
  - *Regra de Negócio:* Proíbe inativação de equipamentos emprestados.

---

### Módulo 4: Usuários Solicitantes
- **RF04.1 — Cadastro de Usuário:**
  - *Campos obrigatórios:* Nome (mín. 3 letras), Tipo (`ALUNO`, `PROFESSOR`, `FUNCIONARIO`, `OUTRO`).
  - *Campos opcionais:* Identificação/Matrícula, Telefone, E-mail.
- **RF04.2 — Listagem e Filtros:**
  - Busca textual (nome, matrícula, e-mail) e filtro por tipo de vínculo.
- **RF04.3 — Inativação com Proteção de Pendências:**
  - *Regra de Negócio:* O sistema impede a inativação de usuários que possuam empréstimos pendentes de devolução.

---

### Módulo 5: Empréstimos (Operação Principal)
- **RF05.1 — Registro Transacional de Empréstimo:**
  - *Campos obrigatórios:* Usuário solicitante, Equipamento, Data de retirada, Previsão de devolução.
  - *Regras de Consistência:*
    1. Apenas usuários ativos podem realizar empréstimo.
    2. Apenas equipamentos disponíveis podem ser selecionados.
    3. Bloqueio pessimista com `FOR UPDATE` para impedir concorrência em lote.
    4. A data de previsão de entrega não pode ser anterior à data de saída.
    5. O status do equipamento é alterado atomicamente para `EMPRESTADO` na mesma transação.

---

### Módulo 6: Devoluções
- **RF06.1 — Processamento de Devolução:**
  - *Identificação:* Seleciona o empréstimo aberto e exibe dados históricos.
  - *Campos:* Data efetiva de devolução, Observações de devolução, Situação final do equipamento (`DISPONIVEL` ou `MANUTENCAO`).
  - *Regras de Consistência:*
    1. Empréstimos já finalizados não aceitam nova devolução.
    2. Atualização atômica do status do empréstimo para `DEVOLVIDO` e do equipamento para a situação final selecionada.

---

### Módulo 7: Relatórios Gerenciais e Exportação
- **RF07.1 — Filtros Consolidados:**
  - Filtro por tipo (todos, abertos, atrasados, devolvidos), por usuário e por intervalo de datas.
- **RF07.2 — Impressão Otimizada (Print CSS):**
  - Oculta menus, cabeçalhos administrativos e botões de ação na impressão (`@media print`), exibindo apenas a tabela com cabeçalho limpo.
- **RF07.3 — Exportação CSV com Proteção:**
  - Gera arquivo `.csv` codificado em UTF-8 (com BOM para compatibilidade com Excel).
  - Sanitiza caracteres especiais no início das células (`=`, `+`, `-`, `@`) para blindar contra vulnerabilidade de *Formula Injection*.
