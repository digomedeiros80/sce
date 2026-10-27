<?php
/**
 * =====================================================================
 * PROJETO: SCE - Sistema de Controle de Empréstimos de Equipamentos
 * ARQUIVO: app/Controllers/AuthController.php
 * DESCRIÇÃO: Controlador de Login, Logout e Instalação do Administrador Inicial
 * =====================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Models\Administrador;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login (ou de instalação inicial se não houver admins)
     */
    public function showLogin(): void
    {
        // Se já estiver logado, redireciona ao dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        // Verifica se há administradores cadastrados
        $adminCount = 0;
        try {
            $adminCount = Administrador::count();
        } catch (\Exception $e) {
            // Em caso de erro de conexão com banco, renderiza tela com instrução amigável
            $this->render('auth/install_error', [
                'errorMessage' => $e->getMessage()
            ], null);
            return;
        }

        // Se não houver administradores, exibe a tela de configuração do primeiro administrador
        if ($adminCount === 0) {
            $this->render('auth/install', [
                'title' => 'Instalação Inicial — Cadastrar Administrador'
            ], null);
            return;
        }

        $this->render('auth/login', [
            'title' => 'Entrar no Sistema'
        ], null);
    }

    /**
     * Processa a autenticação do administrador
     */
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/login');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'email' => 'required|email',
            'senha' => 'required|min:4',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Dados de acesso inválidos.');
            $this->redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        $admin = Administrador::authenticate($email, $senha);

        if (!$admin) {
            $this->setFlashError('E-mail ou senha incorretos, ou usuário inativo.');
            $this->redirect('/login');
        }

        // Autentica com sucesso na sessão
        Auth::login($admin);

        $this->setFlashSuccess("Bem-vindo(a), {$admin['nome']}!");
        $this->redirect('/dashboard');
    }

    /**
     * Processa o cadastro do primeiro administrador
     */
    public function install(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/login');
        }

        // Garante que só pode ser executado se não houver administradores cadastrados
        if (Administrador::count() > 0) {
            $this->setFlashError('O primeiro administrador já foi cadastrado anteriormente.');
            $this->redirect('/login');
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'nome'  => 'required|min:3|max:150',
            'email' => 'required|email|max:150',
            'senha' => 'required|min:6',
        ]);

        if (!$isValid) {
            $this->setFlashError($validator->getFirstError() ?? 'Verifique os campos preenchidos.');
            $this->redirect('/login');
        }

        $nome  = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);

        Administrador::create([
            'nome'  => $nome,
            'email' => $email,
            'senha' => $senha,
        ]);

        $this->setFlashSuccess('Administrador inicial cadastrado com sucesso! Agora realize seu login.');
        $this->redirect('/login');
    }

    /**
     * Encerra a sessão do administrador
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    /**
     * Exibe o formulário de Minha Conta para alteração de dados cadastrais
     */
    public function profile(): void
    {
        $user = Auth::user();
        $admin = Administrador::findById($user['id']);

        $this->render('auth/profile', [
            'title' => 'Minha Conta',
            'admin' => $admin,
        ]);
    }

    /**
     * Processa a atualização do perfil do administrador
     */
    public function updateProfile(): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/perfil');
        }

        $user = Auth::user();
        $validator = new Validator();

        $rules = [
            'nome'  => 'required|min:3|max:150',
            'email' => 'required|email|max:150',
        ];

        if (!empty($_POST['nova_senha'])) {
            $rules['nova_senha'] = 'min:6';
        }

        if (!$validator->validate($_POST, $rules)) {
            $this->setFlashError($validator->getFirstError() ?? 'Dados inválidos.');
            $this->redirect('/perfil');
        }

        Administrador::updateProfile($user['id'], [
            'nome'       => trim($_POST['nome']),
            'email'      => trim($_POST['email']),
            'nova_senha' => !empty($_POST['nova_senha']) ? trim($_POST['nova_senha']) : null,
        ]);

        // Atualiza a sessão com o novo nome
        $_SESSION['admin_nome']  = trim($_POST['nome']);
        $_SESSION['admin_email'] = trim($_POST['email']);

        $this->setFlashSuccess('Perfil atualizado com sucesso.');
        $this->redirect('/perfil');
    }
}
