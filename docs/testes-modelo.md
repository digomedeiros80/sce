# SCE — Sistema de Controle de Empréstimos de Equipamentos
## Documento 4: Referência para Testes e Matriz de Evidências

Este documento registra os procedimentos formais de verificação da aplicação, cobrindo os cenários mandatórios CT01 a CT10. Todos os testes abaixo foram efetivamente executados e verificados via automação em `tests/run_tests.php`.

---

### Matriz de Execução de Testes

| ID | Cenário / Objetivo | Pré-condições | Entradas Utilizadas | Resultado Esperado | Resultado Observado | Situação |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **CT01** | Cadastro de equipamento com dados válidos | Conexão com banco ativa; tabela equipamentos criada | Nome: "Notebook Teste Dell", Patrimônio: "PAT-TEST-01", Status: "DISPONIVEL" | Registro persistido com ID gerado e status DISPONIVEL | Inserido com ID 1 e status DISPONIVEL | **APROVADO** |
| **CT02** | Tentativa de cadastro sem nome | Validador ativo | Nome: "", Patrimônio: "PAT-SEM-NOME" | Rejeição imediata com erro no campo 'nome' | Rejeitado: "O campo 'nome' é obrigatório." | **APROVADO** |
| **CT03** | Tentativa de cadastro com patrimônio duplicado | Equipamento PAT-TEST-01 já cadastrado no CT01 | Nome: "Notebook Repetido", Patrimônio: "PAT-TEST-01" | Lançamento de exceção de violação de unicidade | Exceção disparada: "Já existe um equipamento cadastrado com o patrimônio informado" | **APROVADO** |
| **CT04** | Registro de empréstimo de equipamento disponível | Equipamento DISPONIVEL e usuário ativo | Usuário ID 1, Equipamento ID 1, Previsão: +3 dias | Empréstimo gerado e status do item atualizado para EMPRESTADO | Empréstimo #1 gerado e status atualizado atomicamente para EMPRESTADO | **APROVADO** |
| **CT05** | Tentativa de empréstimo de equipamento já emprestado | Equipamento ID 1 com status EMPRESTADO | Novo empréstimo para Equipamento ID 1 | Bloqueio da transação e rollback | Transação bloqueou: "O equipamento não está disponível (Situação atual: EMPRESTADO)" | **APROVADO** |
| **CT06** | Registro de devolução de empréstimo aberto | Empréstimo #1 em aberto | ID: 1, Data devolução: NOW, Situação: DISPONIVEL | Empréstimo passa para DEVOLVIDO e equipamento volta para DISPONIVEL | Empréstimo #1 finalizado e equipamento retornou para DISPONIVEL | **APROVADO** |
| **CT07** | Tentativa de devolução duplicada | Empréstimo #1 já com status DEVOLVIDO | Nova chamada para devolução do ID 1 | Bloqueio imediato | Exceção disparada: "Este empréstimo já foi finalizado anteriormente" | **APROVADO** |
| **CT08** | Tentativa de acesso administrativo sem autenticação | Sessão vazia/não iniciada | Requisição para rota protegida | Rejeição por `Auth::check()` e redirecionamento | `Auth::check()` retornou `false` | **APROVADO** |
| **CT09** | Tentativa de inativação de usuário com empréstimo aberto | Usuário com empréstimo em aberto ativo | ID do usuário com empréstimo ativo | Bloqueio da alteração cadastral com exceção | Exceção: "Não é permitido inativar um usuário que possui empréstimos em aberto." | **APROVADO** |
| **CT10** | Consulta de empréstimos atrasados | Empréstimo com data prevista no passado | Data prevista anterior à data atual | Contabilização e exibição destacada na listagem e dashboard | `countAtrasados()` contabilizou empréstimos vencidos | **APROVADO** |

---

### Como Executar os Testes Automatizados no Ambiente Local

Para reproduzir a verificação automatizada completa dos 10 cenários, execute no PowerShell ou Prompt de Comando:

```powershell
C:\xampp\php\php.exe c:\xampp\htdocs\sce\tests\run_tests.php
```

A suíte executará de forma isolada e em memória, garantindo integridade sem alterar registros ou dados de produção.
