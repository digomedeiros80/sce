<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: config/database.php
 * DESCRIÇÃO: Parâmetros de conexão com o banco de dados MySQL/MariaDB
 * =====================================================================
 */

return [
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => 3306,
    'database'  => 'sce_db',
    'username'  => 'root',
    'password'  => '', // Altere se o seu MySQL do XAMPP tiver senha definida
    'charset'   => 'utf8mb4',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
