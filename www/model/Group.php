<?php
// file: model/Group.php

require_once(__DIR__."/../config/ValidationException.php");

/**
* Class Group
*
* Represents a Group in the group. A Group was written by an
* specific User (admin) and contains a list of Expenses
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
	private $name;

	/**
	* The content of this group
	* @var string
	*/
	private $description;

	/**
	* The admin of this group
	* @var User
	*/
	private $admin;

	/**
	* The list of expenses of this group
	* @var mixed
	*/
	private $expenses;

	/**
	* The list of members of this group
	* @var mixed
	*/
	private $members;

	/**
	* The constructor
	*
	* @param string $id The id of the group
	* @param string $title The id of the group
	* @param string $content The content of the group
	* @param User $admin The admin of the group
	* @param mixed $expenses The list of expenses
	*/
	public function __construct($id=NULL, $name=NULL, $description=NULL, User $admin=NULL, array $expenses = [], array $members = []) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->admin = $admin;
		$this->expenses = $expenses;
		$this->members = $members;
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
	public function getName() {
		return $this->name;
	}

	/**
	* Sets the title of this group
	*
	* @param string $title the title of this group
	* @return void
	*/
	public function setName($name) {
		$this->name = $name;
	}

	/**
	* Gets the content of this group
	*
	* @return string The content of this group
	*/
	public function getDescription() {
		return $this->description;
	}

	/**
	* Sets the content of this group
	*
	* @param string $content the content of this group
	* @return void
	*/
	public function setDescription($description) {
		$this->description = $description;
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
	* Gets the list of expenses of this group
	*
	* @return mixed The list of expenses of this group
	*/
	public function getExpenses() {
		return $this->expenses;
	}

	/**
	* Sets the expenses of the group
	*
	* @param mixed $expenses the expenses list of this group
	* @return void
	*/
	public function setExpenses(array $expenses) {
		$this->expenses = $expenses;
	}

	public function getMembers() {
		return $this->members;
	}

	public function setMembers(array $members) {
		$this->members = $members;
	}

	public function addMember(User $member, $balance) {
		$this->members[] = ['member' => $member, 'balance' => $balance];
	}

	public function clearMembers() {
		$this->members = []; // Clears the member list
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
		
		// Validaciones existentes
		if (strlen(trim($this->name)) == 0 ) {
			$errors["name"] = "group name is mandatory";
		}
		if (strlen(trim($this->description)) == 0 ) {
			$errors["description"] = "group description is mandatory";
		}
		if ($this->admin == NULL ) {
			$errors["admin"] = "admin is mandatory";
		}
	
		// Nueva validación para los miembros
		if (empty($this->members)) {
			$errors["members"] = "group must have at least one member";
		}
	
		// Validación de objetos miembro
		foreach ($this->members as $member) {
			if (!$member['member'] instanceof User) {
				$errors["members"] = "all members must be instances of User";
				break;
			}
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
	
		try {
			$this->checkIsValidForCreate();
		} catch (ValidationException $ex) {
			foreach ($ex->getErrors() as $key => $error) {
				$errors[$key] = $error;
			}
		}
	
		if (sizeof($errors) > 0) {
			throw new ValidationException($errors, "group is not valid");
		}
	}
}
