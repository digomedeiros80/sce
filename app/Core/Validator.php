<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Core/Validator.php
 * DESCRIÇÃO: Mecanismo flexível de validação e sanitização de dados no servidor
 * =====================================================================
 */

namespace App\Core;

class Validator
{
    /**
     * Erros acumulados de validação
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * Dados limpos/sanitizados após validação
     * @var array
     */
    private array $sanitized = [];

    /**
     * Executa um conjunto de regras sobre uma lista de dados de entrada
     *
     * @param array $data Dados vindos do formulário ($_POST, $_GET, etc)
     * @param array $rules Regras no formato ['campo' => 'required|email|min:3']
     * @return bool True se não houver erros, False caso contrário
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->sanitized = [];

        foreach ($rules as $field => $ruleString) {
            $value = isset($data[$field]) ? trim((string)$data[$field]) : '';
            $this->sanitized[$field] = $value;

            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $this->applyRule($field, $value, $rule, $params);

                // Se já houver erro para este campo, interrompe as demais regras do campo
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplica uma regra específica a um campo
     *
     * @param string $field Nome do campo
     * @param string $value Valor enviado
     * @param string $rule Nome da regra
     * @param array $params Parâmetros da regra
     */
    private function applyRule(string $field, string $value, string $rule, array $params): void
    {
        switch ($rule) {
            case 'required':
                if ($value === '') {
                    $this->errors[$field] = "O campo '{$field}' é obrigatório.";
                }
                break;

            case 'email':
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "O campo '{$field}' deve ser um e-mail válido.";
                }
                break;

            case 'min':
                $min = (int)($params[0] ?? 0);
                if ($value !== '' && mb_strlen($value, 'UTF-8') < $min) {
                    $this->errors[$field] = "O campo '{$field}' deve ter pelo menos {$min} caracteres.";
                }
                break;

            case 'max':
                $max = (int)($params[0] ?? 255);
                if ($value !== '' && mb_strlen($value, 'UTF-8') > $max) {
                    $this->errors[$field] = "O campo '{$field}' não pode exceder {$max} caracteres.";
                }
                break;

            case 'in':
                if ($value !== '' && !in_array($value, $params, true)) {
                    $this->errors[$field] = "O valor informado para '{$field}' é inválido.";
                }
                break;

            case 'integer':
                if ($value !== '' && !ctype_digit(str_replace('-', '', $value))) {
                    $this->errors[$field] = "O campo '{$field}' deve ser um número inteiro.";
                }
                break;

            case 'date':
                if ($value !== '') {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
                    $dts = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if (!$d && !$dt && !$dts) {
                        $this->errors[$field] = "O campo '{$field}' contém formato de data inválido.";
                    }
                }
                break;
        }
    }

    /**
     * Adiciona manualmente uma mensagem de erro customizada para um campo
     *
     * @param string $field Nome do campo
     * @param string $message Mensagem descritiva de erro
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Retorna a lista de mensagens de erro
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna a primeira mensagem de erro encontrada
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Retorna os dados sanitizados
     *
     * @return array
     */
    public function getSanitized(): array
    {
        return $this->sanitized;
    }
}
