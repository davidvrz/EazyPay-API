<?php
// file: model/User.php

require_once(__DIR__."/../config/ValidationException.php");

class User {

	private $username;
	private $passwd;
    private $email;

	public function __construct($username=NULL, $passwd=NULL, $email=NULL) {
		$this->username = $username;
		$this->passwd = $passwd;
        $this->email = $email;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function getPasswd() {
		return $this->passwd;
	}

	public function setPassword($passwd) {
		$this->passwd = $passwd;
	}

    public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}    

	public function checkIsValidForRegister() {
		$errors = array();
		if (strlen($this->username) < 5) {
			$errors["username"] = "error-username-min-length";
		}

		if(strpos($this->username, ' ') !== false){
			$errors["username"] = "error-username-no-spaces";
		}

		if (strlen($this->passwd) < 5) {
			$errors["passwd"] = "error-password-min-length";
		}

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "error-email-format";
        }

		if (sizeof($errors)>0){
			throw new ValidationException($errors, "error-user-invalid");
		}
	}
}
