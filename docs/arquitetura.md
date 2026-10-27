# SCE — Sistema de Controle de Empréstimos de Equipamentos
## Documento 1: Arquitetura do Sistema e Decisões Técnicas

---

### 1. Objetivo da Aplicação
O **SCE (Sistema de Controle de Empréstimos)** é uma aplicação web monolítica voltada para a gestão de recursos patrimoniais em instituições de ensino técnico e superior. O sistema resolve o problema de rastreabilidade de equipamentos (notebooks, projetores multimídia, tablets, cabos e acessórios), controlando quem retirou o item, o prazo estipulado para devolução, os atrasos e o estado de conservação do equipamento.

---

### 2. Estilo Arquitetural e Padrão MVC
A arquitetura do SCE foi concebida sob o padrão **MVC (Model-View-Controller) Simplificado em PHP Orientado a Objetos**, priorizando didática, facilidade de auditoria e manutenção por estudantes de Análise e Desenvolvimento de Sistemas (ADS).

```
                      +-------------------+
                      |   Navegador Web   |
                      +-------------------+
                                | (HTTP Request /sce/public/...)
                                v
                      +-------------------+
                      | public/index.php  | (Front Controller)
                      +-------------------+
                                |
                                v
                        +---------------+
                        |   Router.php  |
                        +---------------+
                                |
            +-------------------+-------------------+
            | (Middleware: Auth / Csrf)             |
            v                                       v
+-----------------------+               +-----------------------+
| EquipamentoController |               |  EmprestimoController | (Controllers)
+-----------------------+               +-----------------------+
            |                                       |
            +-------------------+-------------------+
                                |
                                v
                    +-----------------------+
                    |  Models (Active / RA) | (Database.php PDO)
                    +-----------------------+
                                | (SQL Prepared Statements)
                                v
                    +-----------------------+
                    |    MySQL / MariaDB    | (sce_db)
                    +-----------------------+
                                |
                                v
                    +-----------------------+
                    |   Views (PHP / HTML5) | (layouts/main.php)
                    +-----------------------+
                                | (HTML Output)
                                v
                      +-------------------+
                      |   Navegador Web   |
                      +-------------------+
```

---

### 3. Responsabilidade de Cada Diretório

- **`app/Core/`**: Núcleo da arquitetura:
  - `Database.php`: Singleton responsável pela conexão PDO com MySQL e MariaDB, tratamento silencioso de logs e suporte para injeção de conexões isoladas para testes.
  - `Router.php`: Roteador HTTP simplificado que interpreta métodos (`GET`, `POST`), faz o parsing resiliente de URLs no Apache (compatível com Windows e subdiretórios como `/sce/public/` ou `/sce/`) e aciona os Controllers com middlewares de proteção.
  - `Auth.php`: Mecanismo seguro de autenticação com regeneração de ID de sessão (`session_regenerate_id(true)`), validação de tempo limite de ociosidade (timeout) e proteção contra Fixação de Sessão.
  - `Controller.php`: Classe abstrata que centraliza renderização de views com layout principal, injeção de dados de usuário logado e gerenciamento de mensagens flash (sucesso, aviso, erro).
  - `Csrf.php`: Geração e verificação de tokens CSRF baseados em hash seguro e validação em tempo constante (`hash_equals`).
  - `Validator.php`: Mecanismo centralizado para sanitização e validação de regras de entrada no servidor (`required`, `email`, `min`, `max`, `in`, `integer`, `date`).

- **`app/Controllers/`**: Orquestradores das requisições:
  - `AuthController.php`: Login, logout, primeiro administrador e tela "Minha Conta".
  - `DashboardController.php`: Agregação de métricas reais em tempo real e listagem de atividades recentes.
  - `EquipamentoController.php`: CRUD de itens de inventário, filtros e controle de inativação.
  - `UsuarioController.php`: Gestão de alunos, professores e funcionários.
  - `EmprestimoController.php`: Processamento transacional de empréstimos e devoluções.
  - `RelatorioController.php`: Consultas gerenciais, modo de impressão e exportação CSV com proteção contra CSV Formula Injection.

- **`app/Models/`**: Camada de persistência e regras de consistência de dados:
  - `Administrador.php`, `Usuario.php`, `Equipamento.php`, `Emprestimo.php`.

- **`app/Views/`**: Camada de apresentação em PHP e HTML5, sem lógica de negócio:
  - `layouts/main.php`: Layout mestre com Sidebar responsiva, Topbar, cabeçalho de impressão e container de alertas.
  - `auth/`, `dashboard/`, `equipamentos/`, `usuarios/`, `emprestimos/`, `relatorios/`.

- **`config/`**:
  - `database.php`: Parâmetros do MySQL (host, porta, banco, usuário, senha, opções de PDO).
  - `app.php`: Parâmetros gerais da aplicação (URLs, fuso horário, parâmetros de cookies de sessão).

- **`database/`**:
  - `schema.sql`: Script DDL completo com integridade referencial e índices.
  - `seed.php`: Script de carga com 5 usuários, 10 equipamentos e histórico de empréstimos.

- **`public/`**: Único diretório exposto publicamente na web (`DocumentRoot`), contendo `index.php`, `.htaccess` e ativos estáticos (`assets/css`, `assets/js`, `assets/img`).

- **`storage/logs/`**: Diretório privado de gravação de logs técnicos para auditoria e diagnósticos de erro sem expor exceções ao cliente.

- **`tests/`**: Suíte de testes automatizados executáveis (`run_tests.php`).

---

### 4. Fluxo de Requisições e Segurança
1. A requisição HTTP atinge o Apache e é direcionada pelo `.htaccess` para `public/index.php`.
2. O `Router` extrai o caminho da URI, verifica a rota registrada e executa os middlewares (ex: `auth`).
3. Se o middleware rejeitar, o usuário é direcionado para a tela de login.
4. O Controller recebe a requisição, valida o token CSRF (`validateCsrf()`), valida os dados através do `Validator` e chama os métodos do respectivo Model.
5. As operações críticas de gravação utilizam transações com `beginTransaction()`, bloqueios de concorrência (`FOR UPDATE`) e `commit()` / `rollBack()`.
6. O Controller injeta os dados na View correspondente, que é renderizada dentro do layout padrão.
