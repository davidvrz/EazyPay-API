<?php

require_once '../config/config.php';

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para crear un nuevo usuario
    public function createUser($username, $password) {
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    // Método para obtener un usuario por ID
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Método para obtener todos los usuarios
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Método para actualizar un usuario
    public function updateUser($id, $username, $password) {
        $sql = "UPDATE users SET username = :username, password = :password WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id
        ]);
    }

    // Método para eliminar un usuario
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
?>

