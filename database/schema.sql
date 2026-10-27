-- =====================================================================
-- PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
-- ARQUIVO: database/schema.sql
-- DESCRIÇÃO: Criação do banco de dados e estrutura relacional completa
-- DIALETO: MySQL 8 / MariaDB (utf8mb4)
-- =====================================================================

-- Criação do banco de dados caso não exista
CREATE DATABASE IF NOT EXISTS `sce_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `sce_db`;

-- Desativa temporariamente checagem de chaves estrangeiras para permitir recriação limpa
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- TABELA: administradores
-- Armazena os usuários com permissão de acesso ao painel administrativo
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL COMMENT 'Nome completo do administrador',
    `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'E-mail para autenticação',
    `senha_hash` VARCHAR(255) NOT NULL COMMENT 'Hash da senha gerado por password_hash()',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Ativo, 0 = Inativo',
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro',
    `atualizado_em` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última atualização cadastral'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABELA: usuarios
-- Armazena alunos, professores e funcionários que solicitam empréstimos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL COMMENT 'Nome completo do usuário',
    `email` VARCHAR(150) NULL COMMENT 'E-mail para contato institucional',
    `telefone` VARCHAR(20) NULL COMMENT 'Telefone/WhatsApp para contato',
    `identificacao` VARCHAR(50) NULL COMMENT 'Matrícula acadêmica ou registro institucional',
    `tipo` ENUM('ALUNO', 'PROFESSOR', 'FUNCIONARIO', 'OUTRO') NOT NULL DEFAULT 'ALUNO' COMMENT 'Categoria do solicitante',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Ativo (pode retirar), 0 = Inativo',
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro',
    `atualizado_em` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última alteração de dados'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para busca rápida de usuários
CREATE INDEX `idx_usuarios_nome` ON `usuarios` (`nome`);
CREATE INDEX `idx_usuarios_identificacao` ON `usuarios` (`identificacao`);
CREATE INDEX `idx_usuarios_tipo` ON `usuarios` (`tipo`);
CREATE INDEX `idx_usuarios_ativo` ON `usuarios` (`ativo`);

-- ---------------------------------------------------------------------
-- TABELA: equipamentos
-- Armazena o inventário de equipamentos da instituição
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `equipamentos`;
CREATE TABLE `equipamentos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL COMMENT 'Nome ou título descritivo do equipamento (ex: Notebook Dell Inspiron)',
    `descricao` TEXT NULL COMMENT 'Especificações técnicas, detalhes ou observações',
    `patrimonio` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código de tombamento patrimonial exclusivo',
    `categoria` VARCHAR(100) NULL COMMENT 'Ex: Notebook, Projetor, Cabo, Câmera, Tablet',
    `marca` VARCHAR(100) NULL COMMENT 'Fabricante do item (ex: Dell, Epson, Logitech)',
    `modelo` VARCHAR(100) NULL COMMENT 'Modelo específico do fabricante',
    `numero_serie` VARCHAR(100) NULL COMMENT 'Número de série de fábrica',
    `status` ENUM('DISPONIVEL', 'EMPRESTADO', 'MANUTENCAO', 'INATIVO') NOT NULL DEFAULT 'DISPONIVEL' COMMENT 'Situação atual do equipamento',
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro',
    `atualizado_em` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificação'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para busca e filtros de equipamentos
CREATE INDEX `idx_equipamentos_patrimonio` ON `equipamentos` (`patrimonio`);
CREATE INDEX `idx_equipamentos_nome` ON `equipamentos` (`nome`);
CREATE INDEX `idx_equipamentos_categoria` ON `equipamentos` (`categoria`);
CREATE INDEX `idx_equipamentos_status` ON `equipamentos` (`status`);

-- ---------------------------------------------------------------------
-- TABELA: emprestimos
-- Registro de retiradas e devoluções de equipamentos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `emprestimos`;
CREATE TABLE `emprestimos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL COMMENT 'ID do usuário que retirou o item',
    `equipamento_id` INT NOT NULL COMMENT 'ID do equipamento emprestado',
    `administrador_id` INT NOT NULL COMMENT 'ID do administrador que liberou o empréstimo',
    `data_emprestimo` DATETIME NOT NULL COMMENT 'Data e hora da liberação',
    `data_prevista_devolucao` DATETIME NOT NULL COMMENT 'Prazo limite combinado para entrega',
    `data_devolucao` DATETIME NULL COMMENT 'Data e hora efetiva da devolução (preenchido no encerramento)',
    `observacoes` TEXT NULL COMMENT 'Observações e condições no ato do empréstimo',
    `observacoes_devolucao` TEXT NULL COMMENT 'Observações e estado do item no ato da devolução',
    `status` ENUM('ABERTO', 'DEVOLVIDO') NOT NULL DEFAULT 'ABERTO' COMMENT 'Situação da operação de empréstimo',
    -- Restrições de integridade referencial: impede remoção física para manter histórico
    CONSTRAINT `fk_emprestimos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_emprestimos_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_emprestimos_administrador` FOREIGN KEY (`administrador_id`) REFERENCES `administradores` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para pesquisas e relatórios de empréstimos
CREATE INDEX `idx_emprestimos_status` ON `emprestimos` (`status`);
CREATE INDEX `idx_emprestimos_data_emp` ON `emprestimos` (`data_emprestimo`);
CREATE INDEX `idx_emprestimos_data_prev` ON `emprestimos` (`data_prevista_devolucao`);
CREATE INDEX `idx_emprestimos_usuario_id` ON `emprestimos` (`usuario_id`);
CREATE INDEX `idx_emprestimos_equipamento_id` ON `emprestimos` (`equipamento_id`);

-- Reativa checagem de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;
