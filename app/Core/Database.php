<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Database.php
 * DESCRIÇÃO: Singleton de Conexão com Banco de Dados usando PDO
 * =====================================================================
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    /**
     * Instância única do PDO (Padrão Singleton)
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {}

    /**
     * Método clone privado para impedir clonagem da instância
     */
    private function __clone() {}

    /**
     * Obtém a instância ativa da conexão PDO com o MySQL
     *
     * @return PDO
     * @throws Exception Caso ocorra erro de conexão
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
            
            if (!file_exists($configPath)) {
                throw new Exception("Arquivo de configuração de banco de dados não encontrado.");
            }

            $config = require $configPath;

            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Registra o erro técnico em arquivo protegido sem expor credenciais ao usuário
                self::logError("Falha na conexão PDO: " . $e->getMessage());
                throw new Exception("Não foi possível conectar ao banco de dados MySQL. Verifique se o serviço está ativo no XAMPP.");
            }
        }

        return self::$instance;
    }

    /**
     * Define uma conexão mock ou customizada (útil para testes unitários com SQLite em memória)
     *
     * @param PDO|null $pdo
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Grava logs de erros técnicos da camada de banco de dados
     *
     * @param string $message
     */
    private static function logError(string $message): void
    {
        $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'database_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}
