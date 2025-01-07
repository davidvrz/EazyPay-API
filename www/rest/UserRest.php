<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");
require_once(__DIR__."/../rest/BaseRest.php");

/**
* Class UserRest
*
* It contains operations for adding and checking users' credentials.
* Methods give responses following Restful standards. Methods of this class
* are intended to be mapped as callbacks using the URIDispatcher class.
*
*/
class UserRest extends BaseRest {
    private $userMapper;

    public function __construct() {
        parent::__construct();

        $this->userMapper = new UserMapper();
    }

    public function register($data) {
		if ($this->userMapper->usernameExists($data->username)) {
			header($_SERVER['SERVER_PROTOCOL'].' 409 Conflict');
			header('Content-Type: application/json');
			echo json_encode([
				"message" => "Username already exists"
			]);
			return;
		}

        $user = new User($data->username, $data->password, $data->email);
        try {
            $user->checkIsValidForRegister();

            $this->userMapper->save($user);

            // Enviar respuesta con cabeceras y mensaje informativo
            header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "User registered successfully",
                "username" => $data->username
            ]);
        } catch(ValidationException $e) {
            // Respuesta de error con detalles en formato JSON
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Validation errors",
                "errors" => $e->getErrors()
            ]);
        }
    }

    public function login($username) {
        $currentLogged = parent::authenticateUser();
        if ($currentLogged->getUsername() != $username) {
            // Respuesta de error de autorización
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "You are not authorized to login as anyone but you"
            ]);
        } else {
            // Respuesta exitosa de inicio de sesión
            header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Hello " . $username
            ]);
        }
    }
}

// URI-MAPPING for this Rest endpoint
$userRest = new UserRest();
URIDispatcher::getInstance()
    ->map("GET", "/user/$1", array($userRest, "login"))
    ->map("POST", "/user", array($userRest, "register"));
