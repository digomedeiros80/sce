<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: config/app.php
 * DESCRIÇÃO: Configurações gerais da aplicação (URLs, Fuso Horário, Sessão)
 * =====================================================================
 */

return [
    'name'        => 'SCE — Sistema de Controle de Empréstimos',
    'version'     => '1.0.0',
    'base_url'    => '/sce/public',
    'timezone'    => 'America/Sao_Paulo',
    'session'     => [
        'name'     => 'SCE_SESSION_ID',
        'lifetime' => 7200, // 2 horas
        'path'     => '/',
        'secure'   => false, // Definir true se estiver rodando sobre HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'pagination'  => [
        'per_page' => 10,
    ],
    'log_path'    => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
];
