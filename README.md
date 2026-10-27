# SCE — Sistema de Controle de Empréstimos de Equipamentos

Aplicação web profissional, completa e funcional para gerenciamento de inventário e controle de empréstimos e devoluções de equipamentos em instituições de ensino. Desenvolvido em PHP 8.2+ orientado a objetos com arquitetura MVC simplificada, PDO com Prepared Statements e interface responsiva estilo SaaS com Bootstrap 5.3 e Bootstrap Icons.

---

## 🛠️ Tecnologias Utilizadas

- **Back-end:** PHP 8.2+ (POO, MVC Simplificado, PDO, Prepared Statements, Sessões com cookies HttpOnly/SameSite, CSRF Protection)
- **Front-end:** HTML5, CSS3 Customizado, JavaScript Vanilla, Bootstrap 5.3.3, Bootstrap Icons, Fonte Google Roboto
- **Banco de Dados:** MySQL 8 / MariaDB (charset `utf8mb4_unicode_ci`, Chaves Primárias, Chaves Estrangeiras com integridade referencial, Índices de consulta)
- **Ambiente Recomendado:** Windows 10/11 com XAMPP (Apache + MySQL)

---

## 📁 Estrutura do Projeto

```
sce/
│
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php          # Login, logout, perfil e instalação inicial
│   │   ├── DashboardController.php     # Indicadores em tempo real e listagem recente
│   │   ├── EquipamentoController.php   # CRUD completo e filtros de equipamentos
│   │   ├── UsuarioController.php       # Gestão de alunos, professores e funcionários
│   │   ├── EmprestimoController.php    # Operações transacionais de retirada e devolução
│   │   └── RelatorioController.php     # Relatórios gerenciais, impressão e CSV
│   │
│   ├── Models/
│   │   ├── Administrador.php           # Autenticação e persistência de administradores
│   │   ├── Usuario.php                 # Regras e persistência de usuários
│   │   ├── Equipamento.php             # Inventário patrimonial e status
│   │   └── Emprestimo.php              # Transações SQL, bloqueio e concorrência
│   │
│   ├── Core/
│   │   ├── Database.php                # Singleton PDO com tratamento seguro de erros
│   │   ├── Router.php                  # Roteador HTTP amigável e middlewares
│   │   ├── Auth.php                    # Controle de sessão e segurança
│   │   ├── Controller.php              # Base Controller com views e flash messages
│   │   ├── Csrf.php                    # Gerador e validador de tokens CSRF
│   │   └── Validator.php               # Validador de dados no servidor
│   │
│   └── Views/
│       ├── layouts/main.php            # Layout mestre com Sidebar responsiva e Topbar
│       ├── auth/                       # Telas de login, perfil e instalação inicial
│       ├── dashboard/                  # Visão geral com métricas
│       ├── equipamentos/               # Telas do CRUD de equipamentos
│       ├── usuarios/                   # Telas de cadastro e edição de usuários
│       ├── emprestimos/                # Telas de novo empréstimo e devolução
│       ├── relatorios/                 # Painel de relatórios e consultas
│       └── 404.php                     # Página de erro não encontrado
│
├── config/
│   ├── app.php                         # Configurações de URL, sessão e logs
│   └── database.php                    # Credenciais do MySQL/MariaDB
│
├── database/
│   ├── schema.sql                      # DDL completo das tabelas e índices
│   └── seed.php                        # Carga de dados fictícios para demonstração
│
├── docs/
│   ├── arquitetura.md                  # Documento 1: Arquitetura e Decisões Técnicas
│   ├── requisitos-modelo.md            # Documento 2: Referência para Requisitos
│   ├── modelagem.md                    # Documento 3: Diagramas de Classes, DER e Sequência
│   └── testes-modelo.md                # Documento 4: Matriz de Evidências dos Testes
│
├── public/
│   ├── index.php                       # Ponto de entrada único (Front Controller)
│   ├── .htaccess                       # Reescrita de URLs para o index.php
│   └── assets/                         # CSS customizado, scripts e imagens
│
├── storage/logs/                       # Diretório privado de logs técnicos
├── tests/
│   └── run_tests.php                   # Suíte de testes automatizados (CT01 a CT10)
│
├── .gitignore
├── .htaccess                           # Proteção de pastas internas e redirecionamento
└── README.md
```

---

## 🚀 Instruções de Instalação no Windows com XAMPP

### 1. Local dos Arquivos
Certifique-se de que a pasta do projeto esteja localizada em:
```
C:\xampp\htdocs\sce
```

### 2. Iniciar Serviços no XAMPP
1. Abra o **XAMPP Control Panel**.
2. Clique em **Start** no módulo **Apache**.
3. Clique em **Start** no módulo **MySQL**.

### 3. Criar o Banco de Dados e Importar o Schema
Abra o terminal (PowerShell ou CMD) ou acesse o **phpMyAdmin** (`http://localhost/phpmyadmin/`):

**Via Terminal:**
```powershell
# Criação do banco de dados
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS sce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importação da estrutura relacional (schema.sql)
C:\xampp\mysql\bin\mysql.exe -u root sce_db < C:\xampp\htdocs\sce\database\schema.sql
```

*(Caso o seu usuário root possua senha, adicione o parâmetro `-p`)*.

### 4. Configurar Conexão (Se Necessário)
Se o seu MySQL possui senha, ajuste o arquivo `config/database.php`:
```php
'password' => 'sua_senha_aqui',
```

### 5. Carga de Dados de Demonstração (Opcional)
Para popular o sistema com 5 usuários, 10 equipamentos e empréstimos de exemplo:
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\sce\database\seed.php
```
*Dados padrão inseridos pelo seed:*
- **E-mail:** `admin@instituicao.edu.br`
- **Senha:** `admin123`

### 6. Acessar o Sistema
No seu navegador de preferência, acesse:
👉 **[http://localhost/sce/public/](http://localhost/sce/public/)** ou **[http://localhost/sce/](http://localhost/sce/)**

Se não houver nenhum administrador cadastrado, o sistema direcionará automaticamente para a tela de **Configuração Inicial**, permitindo cadastrar o primeiro gestor com total segurança.

---

## 🧪 Execução dos Testes Automatizados (CT01 a CT10)

Para verificar automaticamente todos os 10 cenários de teste exigidos pelo projeto de forma isolada (sem afetar os dados reais):

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\sce\tests\run_tests.php
```

### Cenários Verificados:
- **CT01:** Cadastro de equipamento com dados válidos.
- **CT02:** Tentativa de cadastro sem nome obrigatório.
- **CT03:** Tentativa de cadastro com patrimônio duplicado.
- **CT04:** Registro de empréstimo de equipamento disponível.
- **CT05:** Bloqueio de empréstimo de equipamento já emprestado.
- **CT06:** Registro de devolução e retorno do equipamento para disponível.
- **CT07:** Bloqueio de tentativa de devolução duplicada.
- **CT08:** Bloqueio de acesso a páginas administrativas sem login.
- **CT09:** Bloqueio de inativação de usuário com empréstimos abertos.
- **CT10:** Consulta e contabilização de empréstimos atrasados.

---

## 📦 Como Publicar o Projeto no GitHub

Para subir este projeto para o seu repositório no GitHub:

1. No terminal do Windows, navegue até a pasta do projeto:
   ```powershell
   cd C:\xampp\htdocs\sce
   ```
2. Inicialize o repositório Git:
   ```powershell
   git init
   git add .
   git commit -m "feat: Implementação completa do Sistema de Controle de Empréstimos (SCE)"
   ```
3. Crie um novo repositório vazio no GitHub e execute:
   ```powershell
   git branch -M main
   git remote add origin https://github.com/SEU_USUARIO/sce.git
   git push -u origin main
   ```
*(O arquivo `.gitignore` já está configurado para não subir credenciais, logs e arquivos temporários)*.

---

## 📚 Documentação Técnica de Apoio Acadêmico

Os documentos desenvolvidos para apoio ao trabalho acadêmico de ADS encontram-se na pasta `docs/`:
- [docs/arquitetura.md](file:///c:/xampp/htdocs/sce/docs/arquitetura.md) — Arquitetura MVC, fluxo e decisões técnicas.
- [docs/requisitos-modelo.md](file:///c:/xampp/htdocs/sce/docs/requisitos-modelo.md) — Inventário completo de requisitos e regras de negócio.
- [docs/modelagem.md](file:///c:/xampp/htdocs/sce/docs/modelagem.md) — Modelagem técnica com diagramas Mermaid (Classes, MER, Dicionário e Sequência).
- [docs/testes-modelo.md](file:///c:/xampp/htdocs/sce/docs/testes-modelo.md) — Matriz formal de evidências de testes.
