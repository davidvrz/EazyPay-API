<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Crear un nuevo usuario
    public function createUser($nombre, $email, $contrasena) {
        try {
            $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
            $sql = "INSERT INTO usuarios (nombre, email, contrasena) VALUES (:nombre, :email, :contrasena)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contrasena', $hashedPassword);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en la base de datos: " . $e->getMessage()); // Guardar el error en el log    
            return false;
        }
    }

    // Autenticar usuario
    public function authenticate($email, $contrasena) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($contrasena, $user['contrasena'])) {
                return $user; // Devolver los datos del usuario si la autenticación es correcta
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en la base de datos: " . $e->getMessage()); // Guardar el error en el log
            return false;
        }
    }

    // Obtener un usuario por su ID
    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM usuarios WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    // Actualizar la información de un usuario
    public function updateUser($id, $nombre, $contrasena = null) {
        try {
            if ($contrasena) {
                $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET nombre = :nombre, contrasena = :contrasena WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':contrasena', $hashedPassword);
            } else {
                $sql = "UPDATE usuarios SET nombre = :nombre WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
            }
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Eliminar un usuario por su ID
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM usuarios WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
