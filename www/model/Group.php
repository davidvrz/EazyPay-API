Group<?php
// file: model/Group.php

require_once(__DIR__."/../config/ValidationException.php");

/**
* Class Group
*
* Represents a Group in the group. A Group was written by an
* specific User (admin) and contains a list of Payments
*
* @admin lipido <lipido@gmail.com>
*/
class Group {

	/**
	* The id of this group
	* @var string
	*/
	private $id;

	/**
	* The title of this group
	* @var string
	*/
	private $title;

	/**
	* The content of this group
	* @var string
	*/
	private $content;

	/**
	* The admin of this group
	* @var User
	*/
	private $admin;

	/**
	* The list of payments of this group
	* @var mixed
	*/
	private $payments;

	/**
	* The constructor
	*
	* @param string $id The id of the group
	* @param string $title The id of the group
	* @param string $content The content of the group
	* @param User $admin The admin of the group
	* @param mixed $payments The list of payments
	*/
	public function __construct($id=NULL, $title=NULL, $content=NULL, User $admin=NULL, array $payments=NULL) {
		$this->id = $id;
		$this->title = $title;
		$this->content = $content;
		$this->admin = $admin;
		$this->payments = $payments;

	}

	/**
	* Gets the id of this group
	*
	* @return string The id of this group
	*/
	public function getId() {
		return $this->id;
	}

	/**
	* Gets the title of this group
	*
	* @return string The title of this group
	*/
	public function getTitle() {
		return $this->title;
	}

	/**
	* Sets the title of this group
	*
	* @param string $title the title of this group
	* @return void
	*/
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	* Gets the content of this group
	*
	* @return string The content of this group
	*/
	public function getContent() {
		return $this->content;
	}

	/**
	* Sets the content of this group
	*
	* @param string $content the content of this group
	* @return void
	*/
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	* Gets the admin of this group
	*
	* @return User The admin of this group
	*/
	public function getAdmin() {
		return $this->admin;
	}

	/**
	* Sets the admin of this group
	*
	* @param User $admin the admin of this group
	* @return void
	*/
	public function setAdmin(User $admin) {
		$this->admin = $admin;
	}

	/**
	* Gets the list of payments of this group
	*
	* @return mixed The list of payments of this group
	*/
	public function getPayments() {
		return $this->payments;
	}

	/**
	* Sets the payments of the group
	*
	* @param mixed $payments the payments list of this group
	* @return void
	*/
	public function setPayments(array $payments) {
		$this->payments = $payments;
	}

	/**
	* Checks if the current instance is valid
	* for being updated in the database.
	*
	* @throws ValidationException if the instance is
	* not valid
	*
	* @return void
	*/
	public function checkIsValidForCreate() {
		$errors = array();
		if (strlen(trim($this->title)) == 0 ) {
			$errors["title"] = "title is mandatory";
		}
		if (strlen(trim($this->content)) == 0 ) {
			$errors["content"] = "content is mandatory";
		}
		if ($this->admin == NULL ) {
			$errors["admin"] = "admin is mandatory";
		}

		if (sizeof($errors) > 0){
			throw new ValidationException($errors, "group is not valid");
		}
	}

	/**
	* Checks if the current instance is valid
	* for being updated in the database.
	*
	* @throws ValidationException if the instance is
	* not valid
	*
	* @return void
	*/
	public function checkIsValidForUpdate() {
		$errors = array();

		if (!isset($this->id)) {
			$errors["id"] = "id is mandatory";
		}

		try{
			$this->checkIsValidForCreate();
		}catch(ValidationException $ex) {
			foreach ($ex->getErrors() as $key=>$error) {
				$errors[$key] = $error;
			}
		}
		if (sizeof($errors) > 0) {
			throw new ValidationException($errors, "group is not valid");
		}
	}
}
