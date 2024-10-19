<?php

require_once('./models/User.php');

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    // Manejar el registro de un nuevo usuario
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            echo('hola');
            // Validaciones básicas
            if (empty($username) || empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios.";
                include './views/users/register.html';
                return;
            }

            // Verificar si el email es válido
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "El email no es válido.";
                include './views/users/register.html';
                return;
            }

            // Crear el usuario en la base de datos
            if ($this->userModel->createUser($username, $email, $password)) {
                header('Location: ../views/projects/index.html'); // Redirigir a la página de inicio
                exit;
            } else {
                $error = "Error al registrar el usuario. El email podría estar en uso.";
                include './views/users/register.html';
            }
        } else {
            include './views/users/register.html';
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $user = $this->userModel->authenticate($email, $password);
            echo('hola');
            if ($user) {
                echo('Entra');
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                echo('autenticado');
                header('Location: ../views/projects/index.html'); // Redirigir a la página de inicio
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                include './views/users/login.html';
            }
        } else {
            include './views/users/login.html';
        }
    }

    // Cerrar sesión
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?controller=user&action=login");
        exit();
    }
}
?>
