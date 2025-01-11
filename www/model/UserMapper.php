<?php
// file: models/UserMapper.php

require_once(__DIR__."/../config/PDOConnection.php");

class UserMapper {

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Saves a User into the database
	*
	* @param User $user The user to be saved
	* @throws PDOException if a database error occurs
	* @return void
	*/
	public function save($user) {
		$stmt = $this->db->prepare("INSERT INTO users (username,passwd,email) values (?,?,?)");
		$stmt->execute(array($user->getUsername(), $user->getPasswd(), $user->getEmail()));
	}

	public function getUser($username) {
        // Preparamos la consulta para buscar el usuario por su nombre de usuario
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        // Recuperamos el resultado de la consulta
        $user_db = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra el usuario, retornamos null
        if ($user_db === false) {
            return null;
        }

        // Crear y retornar el objeto User con los datos obtenidos
        $user = new User(
            $user_db['username'],
            $user_db['passwd'], 
			$user_db['email'],
        );
        
        return $user;
    }

	/**
	* Checks if a given username is already in the database
	*
	* @param string $username the username to check
	* @return boolean true if the username exists, false otherwise
	*/
	public function usernameExists($username) {
		$stmt = $this->db->prepare("SELECT count(username) FROM users where username=?");
		$stmt->execute(array($username));

		return $stmt->fetchColumn() > 0;
	}

	/**
	* Checks if a given pair of username/password exists in the database
	*
	* @param string $username the username
	* @param string $passwd the password
	* @return boolean true the username/passwrod exists, false otherwise.
	*/
	public function isValidUser($username, $passwd) {
		$stmt = $this->db->prepare("SELECT count(username) FROM users where username=? and passwd=?");
		$stmt->execute(array($username, $passwd));

		return $stmt->fetchColumn() > 0;

	}
}
