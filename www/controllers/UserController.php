<?php

require_once '../models/User.php';

class UsersController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    // Método para mostrar el formulario de registro
    public function showRegisterForm() {
        include '../views/users/register.php';
    }

    // Método para manejar el registro de un nuevo usuario
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            // Aquí puedes agregar validaciones para el registro
            $this->userModel->createUser($username, $password);
            header('Location: /index.php'); // Redirigir a la página de inicio
            exit;
        }
        // Si no es un POST, mostrar el formulario de registro
        $this->showRegisterForm();
    }

    // Método para mostrar el formulario de inicio de sesión
    public function showLoginForm() {
        include '../views/users/login.php';
    }

    // Método para manejar el inicio de sesión
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            // Obtener el usuario por nombre de usuario
            $user = $this->userModel->authenticate($username, $password);
            if ($user) {
                // Guardar la sesión del usuario
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: /index.php'); // Redirigir a la página de inicio
                exit;
            } else {
                // Manejar error de inicio de sesión
                $error = "Credenciales incorrectas.";
                include '../views/users/login.php'; // Mostrar la vista de inicio de sesión nuevamente
            }
        } else {
            // Si no es un POST, mostrar el formulario de inicio de sesión
            $this->showLoginForm();
        }
    }

    // Método para mostrar el formulario de edición del usuario
    public function showEditForm($id) {
        $user = $this->userModel->getUserById($id);
        include '../views/users/edit.php'; // Cargar la vista de edición
    }

    // Método para manejar la edición del usuario
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $this->userModel->updateUser($id, $username, $password);
            header('Location: /users/profile.php'); // Redirigir al perfil del usuario
            exit;
        } else {
            // Si no es un POST, mostrar el formulario de edición
            $this->showEditForm($id);
        }
    }

    // Método para manejar la eliminación del usuario
    public function delete($id) {
        $this->userModel->deleteUser($id);
        session_start();
        // Limpiar la sesión y redirigir a la página de inicio
        session_destroy();
        header('Location: /index.php');
        exit;
    }
}
?>
