<?php
session_start();

// Incluimos los controladores necesarios
require_once 'controllers/UsersController.php';
require_once 'controllers/PaymentsController.php';

// Configuración de base de datos
require_once 'config/config.php'; // Asegúrate de que este archivo devuelve la conexión PDO

$usersController = new UsersController($pdo);
$paymentsController = new PaymentsController($pdo);

// Ruta base
$requestUri = $_SERVER['REQUEST_URI'];

// Enrutamiento básico
switch ($requestUri) {
    case '/':
        if (isset($_SESSION['user_id'])) {
            header('Location: /home'); // Si el usuario está autenticado, redirigir a la página de inicio
        } else {
            $usersController->showLoginForm(); // Mostrar el formulario de login por defecto
        }
        break;

    case '/home':
        if (isset($_SESSION['user_id'])) {
            // Aquí mostraríamos la vista con los grupos creados por el usuario
            include 'views/home.php';
        } else {
            header('Location: /');
        }
        break;

    case '/group/payments':
        if (isset($_SESSION['user_id'])) {
            // Lógica para mostrar los pagos de un grupo específico
            $paymentsController->showGroupPayments();
        } else {
            header('Location: /');
        }
        break;

    case '/settings':
        if (isset($_SESSION['user_id'])) {
            // Aquí mostraríamos la página de configuración
            include 'views/settings.php';
        } else {
            header('Location: /');
        }
        break;

    case '/register':
        $usersController->register(); // Manejar el registro de usuarios
        break;

    case '/login':
        $usersController->login(); // Manejar el inicio de sesión
        break;

    case '/logout':
        session_destroy();
        header('Location: /');
        break;

    default:
        // En caso de ruta no válida
        http_response_code(404);
        echo "Página no encontrada.";
        break;
}
