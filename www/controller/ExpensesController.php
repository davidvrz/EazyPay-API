<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/Expense.php");

require_once(__DIR__."/../model/GroupMapper.php");
require_once(__DIR__."/../model/ExpenseMapper.php");
require_once(__DIR__."/../model/UserMapper.php");


require_once(__DIR__."/../controller/BaseController.php");

class ExpensesController extends BaseController {

	private $expenseMapper;
	private $userMapper;
	private $groupMapper;

	public function __construct() {
		parent::__construct();

		$this->expenseMapper = new ExpenseMapper();
		$this->groupMapper = new GroupMapper();
		$this->userMapper = new UserMapper();
	}


	public function add() {

		if (!isset($_REQUEST["group_id"])) {
			throw new Exception("A group id is mandatory");
		}

		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Adding groups requires login");
		}

		$groupId = $_REQUEST["group_id"];  
		$group = $this->groupMapper->getGroupDetailsById($groupId);

		if ($group === null) {
			throw new Exception("No such group with id: " . $groupId);
		}

		$expense = new Expense();

		if (isset($_POST["submit"])) {

			// Create and populate the Expense object
			$expense->setDescription($_POST["description"]);
			$expense->setGroup($group);
			$expense->setTotalAmount($_POST["totalAmount"]);
			$payer = $this->userMapper->getUser($_POST["payer"]);
			$expense->setPayer($payer);
			
			$participants = $_POST["participants"];

			foreach ($participants as $username => $amount) {
				$user = $this->userMapper->getUser($username);
				if ($user && floatval($amount) > 0) {
					$roundedAmount = round(floatval($amount), 2);
					$expense->addParticipant($user, $roundedAmount);
				} elseif (!$user) {
					$errors[] = "User $username not found";
				} elseif (floatval($amount) <= 0) {
					$errors['participants'][$username] = "Amount for $username must be greater than 0";
				}
			}
			
			try {

				// validate Expense object
				$expense->checkIsValidForCreate();

				// save the Expense object into the database
				$this->expenseMapper->save($expense);

				// POST-REDIRECT-GET
				$this->view->setFlash(sprintf(i18n("Expense \"%s\" successfully added."),$group->getName()));

				$this->view->redirect("groups", "view", "id=".$group->getId());
			}catch(ValidationException $ex) {
				$errors = $ex->getErrors();
				
				$this->view->setVariable("errors", $errors, true);

				$this->view->redirect("groups", "view", "id=".$group->getId());
			}
		} 

		// Put the expense object visible to the view
		$this->view->setVariable("expense", $expense);

		// Put the group object visible to the view
		$this->view->setVariable("group", $group);

		// render the view (/view/posts/add.php)
		$this->view->render("expenses", "add");

	}

	public function view() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Viewing expenses requires login");
		}

		if (!isset($_GET["id"])) {
			throw new Exception("Expense ID is missing");
		}

		$expenseId = $_GET["id"];
		$expense = $this->expenseMapper->getExpenseDetailsById($expenseId);

		if ($expense === null) {
			throw new Exception("No such expense with ID: " . $expenseId);
		}

		// Set the group to be accesible in the view
		$groupId = $expense->getGroup()->getId();
		$group = $this->groupMapper->getGroupDetailsById($groupId);
		$this->view->setVariable("group", $group);	

		// Set the expense to be accessible in the view
		$this->view->setVariable("expense", $expense);

		// Render the expense view page
		$this->view->render("expenses", "view");
	}


	public function edit() {
		if (!isset($_REQUEST["id"])) {
			throw new Exception("An expense id is mandatory");
		}
	
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Editing expenses requires login");
		}
	
		$expenseId = $_REQUEST["id"];
		$expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
	
		if ($expense == NULL) {
			throw new Exception("No such expense with id: ".$expenseId);
		}

		$groupId = $expense->getGroup()->getId();  
		$group = $this->groupMapper->getGroupDetailsById($groupId);
	
		if ($group == NULL) {
			throw new Exception("Group not found");
		}
	
		// Verify if currentUser is the payer o the admin
		if (!($expense->getPayer() == $this->currentUser || $group->getAdmin() == $this->currentUser)) {
			throw new Exception("Logged user is neither the payer nor the admin");
		}
	
		if (isset($_POST["submit"])) { // reaching via HTTP POST
	
			// Create and populate the Expense object
			$expense->setDescription($_POST["description"]);
			$expense->setTotalAmount($_POST["totalAmount"]);
			$payer = $this->userMapper->getUser($_POST["payer"]);
			$expense->setPayer($payer);
	
			// Obtain expense pariticipants
			$participants = $_POST["participants"];
		
			$newParticipants = [];
	
			foreach ($participants as $username => $amount) {
				$user = $this->userMapper->getUser($username);
				if ($user && floatval($amount) > 0) {
					$newParticipants[$username] = round(floatval($amount), 2);
				} elseif (!$user) {
					$errors[] = "User $username not found";
				} elseif (floatval($amount) <= 0) {
					$errors['participants'][$username] = "Amount for $username must be greater than 0";
				}
			}
	
			$expense->clearParticipants(); 
			foreach ($newParticipants as $username => $amount) {
				$user = $this->userMapper->getUser($username);
				if ($user) {
					$expense->addParticipant($user, $amount);
				}
			}
	
			try {
				$expense->checkIsValidForUpdate();
				// Update expense object in database
				$this->expenseMapper->update($expense);
	
				// POST-REDIRECT-GET
				$this->view->setFlash(sprintf(i18n("Expense \"%s\" successfully updated."), $expense->getDescription()));
				$this->view->redirect("expenses", "view", "id=".$expense->getId());
	
			} catch (ValidationException $ex) {
				$errors = $ex->getErrors();
				$this->view->setVariable("errors", $errors);
			}
		}
	
		// Put the group object visible to the view
		$this->view->setVariable("group", $group);

		// Put the expense object visible to the view
		$this->view->setVariable("expense", $expense);

		// render the view (/view/posts/add.php)
		$this->view->render("expenses", "edit");
	}
	

	public function delete() {
		if (!isset($_GET["id"])) {
			throw new Exception("id is mandatory");
		}
		
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Deleting expenses requires login");
		}
	
		$expenseId = $_REQUEST["id"];
		$expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
	
		if ($expense == null) {
			throw new Exception("No such expense with id: ".$expenseId);
		}
	
		$groupid = $expense->getGroup()->getId();

		if ($groupid == null) {
			throw new Exception("Expense does not belong to any group");
		}

		$group = $this->groupMapper->findById($groupid);

		// Verify if currentUser is the payer o the admin
		if (!($expense->getPayer() == $this->currentUser || $group->getAdmin() == $this->currentUser)) {
			throw new Exception("Logged user is neither the payer nor the admin");
		}
	
		// Delete expense from database
		$this->expenseMapper->delete($expense);
	
		// POST-REDIRECT-GET
		$this->view->setFlash(sprintf(i18n("Expense \"%s\" successfully deleted."), $expense->getDescription()));
		$this->view->redirect("groups", "view", "id=".$group->getId());
	}
	
	
}
