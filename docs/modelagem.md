# SCE — Sistema de Controle de Empréstimos de Equipamentos
## Documento 3: Modelagem Técnica e Diagramas do Sistema

---

### 1. Diagrama de Classes (UML / Mermaid)

```mermaid
classDiagram
    class Administrador {
        +int id
        +string nome
        +string email
        +string senha_hash
        +int ativo
        +string criado_em
        +string atualizado_em
        +findByEmail(string email) Administrador
        +findById(int id) Administrador
        +create(array data) int
        +authenticate(string email, string senha) bool
        +count() int
        +updateProfile(int id, array data) bool
    }

    class Usuario {
        +int id
        +string nome
        +string email
        +string telefone
        +string identificacao
        +string tipo
        +int ativo
        +string criado_em
        +string atualizado_em
        +paginate(string busca, string tipo, int page, int perPage) array
        +findById(int id) Usuario
        +getActiveList() array
        +create(array data) int
        +update(int id, array data) bool
        +inactivate(int id) bool
        +activate(int id) bool
        +hasOpenLoans(int id) bool
        +countActive() int
    }

    class Equipamento {
        +int id
        +string nome
        +string descricao
        +string patrimonio
        +string categoria
        +string marca
        +string modelo
        +string numero_serie
        +string status
        +string criado_em
        +string atualizado_em
        +paginate(string busca, string pat, string cat, string status, int page, int perPage) array
        +findById(int id) Equipamento
        +findByPatrimonio(string patrimonio) Equipamento
        +getDistinctCategories() array
        +getAvailableList() array
        +create(array data) int
        +update(int id, array data) bool
        +setStatus(int id, string status) bool
        +inactivate(int id) bool
        +hasOpenLoan(int id) bool
        +getMetrics() array
    }

    class Emprestimo {
        +int id
        +int usuario_id
        +int equipamento_id
        +int administrador_id
        +string data_emprestimo
        +string data_prevista_devolucao
        +string data_devolucao
        +string observacoes
        +string observacoes_devolucao
        +string status
        +paginate(int userId, int eqId, string status, string dIni, string dFim, int page, int perPage) array
        +findById(int id) Emprestimo
        +registrarEmprestimo(array data) int
        +registrarDevolucao(int id, string dataDev, string obs, string situacaoFinal) bool
        +countAtrasados() int
        +getLatest(int limit) array
        +getReportData(array filtros) array
    }

    Administrador "1" --> "0..*" Emprestimo : registra
    Usuario "1" --> "0..*" Emprestimo : solicita
    Equipamento "1" --> "0..*" Emprestimo : é objeto de
```

---

### 2. Modelo Entidade-Relacionamento (MER / DER)

```mermaid
erDiagram
    ADMINISTRADORES ||--o{ EMPRESTIMOS : autoriza
    USUARIOS ||--o{ EMPRESTIMOS : retira
    EQUIPAMENTOS ||--o{ EMPRESTIMOS : compoe

    ADMINISTRADORES {
        int id PK
        string nome
        string email UK
        string senha_hash
        int ativo
        datetime criado_em
        datetime atualizado_em
    }

    USUARIOS {
        int id PK
        string nome
        string email
        string telefone
        string identificacao
        enum tipo
        int ativo
        datetime criado_em
        datetime atualizado_em
    }

    EQUIPAMENTOS {
        int id PK
        string nome
        text descricao
        string patrimonio UK
        string categoria
        string marca
        string modelo
        string numero_serie
        enum status
        datetime criado_em
        datetime atualizado_em
    }

    EMPRESTIMOS {
        int id PK
        int usuario_id FK
        int equipamento_id FK
        int administrador_id FK
        datetime data_emprestimo
        datetime data_prevista_devolucao
        datetime data_devolucao
        text observacoes
        text observacoes_devolucao
        enum status
    }
```

---

### 3. Dicionário de Dados Técnico

| Tabela | Coluna | Tipo | Tam | Nulo | Chave | Descrição Técnica |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **administradores** | id | INT | 11 | NÃO | PK | Identificador único autoincremento |
| | nome | VARCHAR | 150 | NÃO | - | Nome completo do gestor administrativo |
| | email | VARCHAR | 150 | NÃO | UK | E-mail para autenticação de acesso |
| | senha_hash | VARCHAR | 255 | NÃO | - | Hash criptográfico gerado por BCRYPT |
| | ativo | TINYINT | 1 | NÃO | - | 1 = Ativo, 0 = Inativo |
| | criado_em | DATETIME | - | NÃO | - | Data e hora de criação do cadastro |
| | atualizado_em | DATETIME | - | SIM | - | Timestamp da última atualização |
| **usuarios** | id | INT | 11 | NÃO | PK | Identificador único autoincremento |
| | nome | VARCHAR | 150 | NÃO | - | Nome completo do solicitante |
| | email | VARCHAR | 150 | SIM | - | E-mail de contato do solicitante |
| | telefone | VARCHAR | 20 | SIM | - | Telefone ou WhatsApp de contato |
| | identificacao | VARCHAR | 50 | SIM | - | Matrícula acadêmica ou registro |
| | tipo | ENUM | - | NÃO | - | ALUNO, PROFESSOR, FUNCIONARIO, OUTRO |
| | ativo | TINYINT | 1 | NÃO | - | 1 = Ativo (apto), 0 = Inativo (bloqueado) |
| **equipamentos** | id | INT | 11 | NÃO | PK | Identificador único autoincremento |
| | nome | VARCHAR | 150 | NÃO | - | Descrição/Título do equipamento |
| | descricao | TEXT | - | SIM | - | Detalhes, acessórios e especificações |
| | patrimonio | VARCHAR | 50 | NÃO | UK | Código exclusivo de tombamento patrimonial |
| | categoria | VARCHAR | 100 | SIM | - | Classificação (ex: Notebook, Projetor) |
| | marca | VARCHAR | 100 | SIM | - | Fabricante do item |
| | modelo | VARCHAR | 100 | SIM | - | Modelo técnico do equipamento |
| | numero_serie | VARCHAR | 100 | SIM | - | Serial number de fábrica |
| | status | ENUM | - | NÃO | - | DISPONIVEL, EMPRESTADO, MANUTENCAO, INATIVO |
| **emprestimos** | id | INT | 11 | NÃO | PK | Identificador único autoincremento |
| | usuario_id | INT | 11 | NÃO | FK | Chave estrangeira referenciando `usuarios(id)` |
| | equipamento_id| INT | 11 | NÃO | FK | Chave estrangeira referenciando `equipamentos(id)` |
| | administrador_id| INT | 11 | NÃO | FK | Chave estrangeira referenciando `administradores(id)` |
| | data_emprestimo| DATETIME | - | NÃO | - | Data e hora em que o item foi retirado |
| | data_prevista_devolucao| DATETIME | - | NÃO | - | Prazo estipulado para entrega |
| | data_devolucao| DATETIME | - | SIM | - | Data e hora efetiva de entrega (baixa) |
| | observacoes | TEXT | - | SIM | - | Observações no momento do empréstimo |
| | observacoes_devolucao| TEXT | - | SIM | - | Observações no momento do recebimento |
| | status | ENUM | - | NÃO | - | ABERTO, DEVOLVIDO |

---

### 4. Diagrama de Sequência: Registro de Empréstimo Transacional

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrador
    participant View as Interface (View)
    participant Controller as EmprestimoController
    participant Model as Emprestimo (Model)
    participant DB as Banco de Dados (MySQL)

    Admin->>View: Preenche formulário e clica em "Confirmar Empréstimo"
    View->>Controller: POST /sce/public/emprestimos/novo (com CsrfToken)
    Controller->>Controller: Valida CsrfToken e dados com Validator
    Controller->>Model: registrarEmprestimo(data)
    
    Model->>DB: beginTransaction()
    Model->>DB: SELECT ativo FROM usuarios WHERE id = ? FOR UPDATE
    DB-->>Model: Usuário ativo (OK)
    
    Model->>DB: SELECT status FROM equipamentos WHERE id = ? FOR UPDATE
    DB-->>Model: Equipamento 'DISPONIVEL' (OK)
    
    Model->>DB: SELECT id FROM emprestimos WHERE equipamento_id = ? AND status = 'ABERTO'
    DB-->>Model: Nenhum registro em aberto (OK)
    
    Model->>DB: INSERT INTO emprestimos (...) VALUES (...)
    DB-->>Model: Retorna emprestimo_id gerado
    
    Model->>DB: UPDATE equipamentos SET status = 'EMPRESTADO' WHERE id = ?
    DB-->>Model: Atualização confirmada (1 linha afetada)
    
    Model->>DB: commit()
    Model-->>Controller: Retorna ID do empréstimo com sucesso
    Controller-->>View: Redireciona com FlashMessage de sucesso
    View-->>Admin: Exibe tela com confirmação e equipamento como "EMPRESTADO"
```
