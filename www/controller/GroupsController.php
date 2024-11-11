<?php
//file: controller/GroupController.php

require_once(__DIR__."/../model/Expense.php");
require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/GroupMapper.php");
require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");

require_once(__DIR__."/../config/ViewManager.php");
require_once(__DIR__."/../controller/BaseController.php");

/**
* Class GroupsController
*
* Controller to make a CRUDL of Groups entities
*
*/
class GroupsController extends BaseController {

	/**
	* Reference to the GroupMapper to interact
	* with the database
	*
	* @var GroupMapper
	*/
	private $groupMapper;
	private $userMapper;

	public function __construct() {
		parent::__construct();

		$this->groupMapper = new GroupMapper();
		$this->userMapper = new UserMapper();
	}

	/**
	* Action to list groups
	*
	* Loads all the groups from the database.
	* No HTTP parameters are needed.
	*
	* The views are:
	* <ul>
	* <li>groups/index (via include)</li>
	* </ul>
	*/
	public function index() {

		// obtain the data from the database
		$groups = $this->groupMapper->findAll();

		// put the array containing Group object to the view
		$this->view->setVariable("groups", $groups);

		// render the view (/view/groups/index.php)
		$this->view->render("groups", "index");
	}

	/**
	* Action to view a given group
	*
	* This action should only be called via GET
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the group (via HTTP GET)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>groups/view: If group is successfully loaded (via include).	Includes these view variables:</li>
	* <ul>
	*	<li>group: The current Group retrieved</li>
	*	<li>expense: The current Expense instance, empty or
	*	being added (but not validated)</li>
	* </ul>
	* </ul>
	*
	* @throws Exception If no such group of the given id is found
	* @return void
	*
	*/
	public function view(){
		if (!isset($_GET["id"])) {
			throw new Exception("id is mandatory");
		}

		$groupid = $_GET["id"];

		// find the Group object in the database
		$group = $this->groupMapper->getGroupDetailsById($groupid);

		if ($group == NULL) {
			throw new Exception("no such group with id: ".$groupid);
		}

		// put the Group object to the view
		$this->view->setVariable("group", $group);

		// render the view (/view/groups/view.php)
		$this->view->render("groups", "view");

	}

	/**
	* Action to add a new group
	*
	* When called via GET, it shows the add form
	* When called via POST, it adds the group to the
	* database
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>name: Name of the group (via HTTP POST)</li>
	* <li>description: Description of the group (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>groups/add: If this action is reached via HTTP GET (via include)</li>
	* <li>groups/index: If group was successfully added (via redirect)</li>
	* <li>groups/add: If validation fails (via include). Includes these view variables:</li>
	* <ul>
	*	<li>group: The current Group instance, empty or
	*	being added (but not validated)</li>
	*	<li>errors: Array including per-field validation errors</li>
	* </ul>
	* </ul>
	* @throws Exception if no user is in session
	* @return void
	*/
	public function add() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Adding groups requires login");
		}

		$group = new Group();

		if (isset($_POST["submit"])) { // reaching via HTTP Group...

			// populate the Group object with data form the form
			$group->setName($_POST["name"]);
			$group->setDescription($_POST["description"]);

			// The user of the Group is the currentUser (user in session)
			$group->setAdmin($this->currentUser);

			// Add the current user as a permanent member of the group
			$group->addMember($this->currentUser);

			// Añadir los participantes (miembros) al grupo
			if (isset($_POST["members"])) {
				foreach ($_POST["members"] as $memberName) {
					$user = $this->userMapper->getUser($memberName);
					// Verificar si el usuario existe en la base de datos
					if ($user && $user != $this->currentUser) {
						$group->addMember($user);
					} else {
						// Manejar el error si no se encuentra el usuario
						$errors[] = "User $memberName not found";
					}
				}
			}

			try {
				// validate Group object
				$group->checkIsValidForCreate(); // if it fails, ValidationException

				// save the Group object into the database
				$this->groupMapper->save($group);

				// POST-REDIRECT-GET
				// Everything OK, we will redirect the user to the list of groups
				// We want to see a message after redirection, so we establish
				// a "flash" message (which is simply a Session variable) to be
				// get in the view after redirection.
				$this->view->setFlash(sprintf(i18n("Group \"%s\" successfully added."),$group ->getName()));

				// perform the redirection. More or less:
				// header("Location: index.php?controller=groups&action=index")
				// die();
				$this->view->redirect("groups", "index");

			}catch(ValidationException $ex) {
				// Get the errors array inside the exepction...
				$errors = $ex->getErrors();
				// And put it to the view as "errors" variable
				$this->view->setVariable("errors", $errors);
			}
		}

		// Put the Group object visible to the view
		$this->view->setVariable("group", $group);

		// render the view (/view/groups/add.php)
		$this->view->render("groups", "add");

	}

	/**
	* Action to edit a group
	*
	* When called via GET, it shows an edit form
	* including the current data of the Group.
	* When called via POST, it modifies the group in the
	* database.
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the group (via HTTP POST and GET)</li>
	* <li>name: Name of the group (via HTTP POST)</li>
	* <li>description: Description of the group (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>groups/edit: If this action is reached via HTTP GET (via include)</li>
	* <li>groups/index: If group was successfully edited (via redirect)</li>
	* <li>groups/edit: If validation fails (via include). Includes these view variables:</li>
	* <ul>
	*	<li>group: The current Group instance, empty or being added (but not validated)</li>
	*	<li>errors: Array including per-field validation errors</li>
	* </ul>
	* </ul>
	* @throws Exception if no id was provided
	* @throws Exception if no user is in session
	* @throws Exception if there is not any group with the provided id
	* @throws Exception if the current logged user is not the admin of the group
	* @return void
	*/
	public function edit() {
		if (!isset($_REQUEST["id"])) {
			throw new Exception("A group id is mandatory");
		}

		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Editing groups requires login");
		}


		// Get the Group object from the database
		$groupid = $_REQUEST["id"];
		$group = $this->groupMapper->getGroupDetailsById($groupid);

		// Does the group exist?
		if ($group == NULL) {
			throw new Exception("no such group with id: ".$groupid);
		}

		// Check if the Group admin is the currentUser (in Session)
		if ($group->getAdmin() != $this->currentUser) {
			throw new Exception("logged user is not the admin of the group id ".$groupid);
		}

		if (isset($_POST["submit"])) { // reaching via HTTP Group...

			// populate the Group object with data form the form
			$group->setName($_POST["name"]);
			$group->setDescription($_POST["description"]);

			// Update the members
			if (isset($_POST["members"])) {
				// Clear existing members
				$group->clearMembers();

				foreach ($_POST["members"] as $memberName) {
					$user = $this->userMapper->getUser($memberName);
					if ($user){
						// Check if the member is not the admin to avoid duplication
						if ($user != $group->getAdmin()) {
							$group->addMember($user);
						}
					}
					else {
						// Handle the error if the user is not found
						$errors[] = "User $memberName not found";
					}
				}
			}

			try {
				// validate Group object
				$group->checkIsValidForUpdate(); // if it fails, ValidationException
				// update the Group object in the database
				$this->groupMapper->update($group);

				// POST-REDIRECT-GET
				// Everything OK, we will redirect the user to the list of groups
				// We want to see a message after redirection, so we establish
				// a "flash" message (which is simply a Session variable) to be
				// get in the view after redirection.
				$this->view->setFlash(sprintf(i18n("Group \"%s\" successfully updated."),$group ->getName()));

				// perform the redirection. More or less:
				// header("Location: index.php?controller=groups&action=index")
				// die();
				$this->view->redirect("groups", "index");

			}catch(ValidationException $ex) {
				// Get the errors array inside the exepction...
				$errors = $ex->getErrors();
				// And put it to the view as "errors" variable
				$this->view->setVariable("errors", $errors);
			}
		}

		// Put the Group object visible to the view
		$this->view->setVariable("group", $group);

		// render the view (/view/groups/add.php)
		$this->view->render("groups", "edit");
	}

	/**
	* Action to delete a group
	*
	* This action should only be called via HTTP POST
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the group (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>groups/index: If group was successfully deleted (via redirect)</li>
	* </ul>
	* @throws Exception if no id was provided
	* @throws Exception if no user is in session
	* @throws Exception if there is not any group with the provided id
	* @throws Exception if the admin of the group to be deleted is not the current user
	* @return void
	*/
	public function delete() {
		if (!isset($_POST["id"])) {
			throw new Exception("id is mandatory");
		}
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Editing groups requires login");
		}
		
		// Get the Group object from the database
		$groupid = $_REQUEST["id"];
		$group = $this->groupMapper->findById($groupid);

		// Does the group exist?
		if ($group == NULL) {
			throw new Exception("no such group with id: ".$groupid);
		}

		// Check if the Group admin is the currentUser (in Session)
		if ($group->getAdmin() != $this->currentUser) {
			throw new Exception("Group admin is not the logged user");
		}

		// Delete the Group object from the database
		$this->groupMapper->delete($group);

		// POST-REDIRECT-GET
		// Everything OK, we will redirect the user to the list of groups
		// We want to see a message after redirection, so we establish
		// a "flash" message (which is simply a Session variable) to be
		// get in the view after redirection.
		$this->view->setFlash(sprintf(i18n("Group \"%s\" successfully deleted."),$group ->getName()));

		// perform the redirection. More or less:
		// header("Location: index.php?controller=groups&action=index")
		// die();
		$this->view->redirect("groups", "index");

	}
}

