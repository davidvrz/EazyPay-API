<?php
// index.php - Punto de entrada de la aplicación

// 1. Cargar Configuración e Incluir Archivos Necesarios
require_once './config/config.php'; // Archivo con la configuración de la base de datos y otros parámetros

// Autocargar clases de controladores, modelos y librerías
spl_autoload_register(function ($className) {
    // Revisar si la clase pertenece a 'controllers', 'models' o 'libs'
    $paths = [
        './controllers/' . $className . '.php',
        './models/' . $className . '.php',
        './libs/' . $className . '.php', // Si tienes librerías adicionales, se pueden cargar desde aquí
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// 2. Función para Gestionar las Rutas
function routeRequest($pdo) {
    // Obtener la acción de la URL o establecer un valor por defecto
    $controllerName = isset($_GET['controller']) ? ucfirst($_GET['controller']) : 'user';
    $action = isset($_GET['action']) ? $_GET['action'] : 'register'; // Por defecto, se dirige a la página de registro
    $controllerClassName = ucfirst($controllerName) . 'Controller';

    // 3. Verificar si el controlador solicitado existe
    if (file_exists('./controllers/' . $controllerClassName . '.php')) {
        // Crear una instancia del controlador
        $controller = new $controllerClassName($pdo);

        // Verificar si el método (acción) solicitado existe en el controlador
        if (method_exists($controller, $action)) {
            // Ejecutar la acción
            $controller->$action();
        } else {
            // Acción no encontrada, mostrar error 404
            show404();
        }
    } else {
        // Controlador no encontrado, mostrar error 404
        show404();
    }
}

// 4. Función para Mostrar Página de Error 404
function show404() {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Error 404 - Página no encontrada</h1>";
    echo "<p>Lo sentimos, la página que estás buscando no existe.</p>";
    exit();
}

// 5. Ejecutar la Solicitud
routeRequest($pdo);
