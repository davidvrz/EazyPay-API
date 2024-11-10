<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/Expense.php");

require_once(__DIR__."/../model/GroupMapper.php");
require_once(__DIR__."/../model/ExpenseMapper.php");
require_once(__DIR__."/../model/UserMapper.php");


require_once(__DIR__."/../controller/BaseController.php");

/**
* Class ExpensesController
*
* Controller for expenses related use cases.
*
* @author lipido <lipido@gmail.com>
*/
class ExpensesController extends BaseController {

	/**
	* Reference to the ExpenseMapper to interact
	* with the database
	*
	* @var ExpenseMapper
	*/
	private $expenseMapper;
	private $userMapper;


	/**
	* Reference to the GroupMapper to interact
	* with the database
	*
	* @var GroupMapper
	*/
	private $groupMapper;

	public function __construct() {
		parent::__construct();

		$this->expenseMapper = new ExpenseMapper();
		$this->groupMapper = new GroupMapper();
		$this->userMapper = new UserMapper();
	}

	/**
	* Action to adds a expense to a group
	*
	* This method should only be called via HTTP POST.
	*
	* The user of the expense is taken from the {@link BaseController::currentUser}
	* property.
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the group (via HTTP POST)</li>
	* <li>content: Content of the expense (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>groups/view?id=group: If expense was successfully added of,
	* or if it was not validated (via redirect). Includes these view variables:</li>
	* <ul>
	*	<li>errors (flash): Array including per-field validation errors</li>
	*	<li>expense (flash): The current Expense instance, empty or being added</li>
	* </ul>
	* </ul>
	*
	* @return void
	*/
	public function add() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Adding groups requires login");
		}

		if (isset($_GET["group_id"])) {
			$groupid = $_GET["group_id"];
		} elseif (isset($_POST["group_id"])) {
			$groupid = $_POST["group_id"];
		} else {
			throw new Exception("group_id is missing");
		}

		$group = $this->groupMapper->getGroupDetailsById($groupid);
		if ($group === null) {
			throw new Exception("No such group with id: " . $groupid);
		}

		$this->view->setVariable("group", $group);

		$expense = new Expense();

		if (isset($_POST["submit"])) { // reaching via HTTP Post...
			// Create and populate the Expense object
			$expense->setDescription($_POST["description"]);
			$expense->setGroup($group);
			$expense->setTotalAmount($_POST["totalAmount"]);
			$payer = $this->userMapper->getUser($_POST["payer"]);
			$expense->setPayer($payer);
			
			$participants = $_POST["participants"]; // Esta variable debería contener una lista de los participantes
			$amountPerParticipant = $expense->getTotalAmount() / count($participants);

			foreach ($participants as $username) {
				$user = $this->userMapper->getUser($username);
				if ($user) {
					$expense->addParticipant($user, $amountPerParticipant);
				} else {
					// Manejar el error si no se encuentra el usuario
					$errors[] = "User $username not found";
				}  
			}

			try {

				// validate Expense object
				$expense->checkIsValidForCreate(); // if it fails, ValidationException

				// save the Expense object into the database
				$this->expenseMapper->save($expense);

				// POST-REDIRECT-GET
				// Everything OK, we will redirect the user to the list of groups
				// We want to see a message after redirection, so we establish
				// a "flash" message (which is simply a Session variable) to be
				// get in the view after redirection.
				$this->view->setFlash(sprintf(i18n("Expense \"%s\" successfully added."),$group->getName()));

				// perform the redirection. More or less:
				// header("Location: index.php?controller=groups&action=view&id=$groupid")
				// die();
				$this->view->redirect("groups", "view", "id=".$group->getId());
			}catch(ValidationException $ex) {
				$errors = $ex->getErrors();

				// Go back to the form to show errors.
				// However, the form is not in a single page (expenses/add)
				// It is in the View Post page.
				// We will save errors as a "flash" variable (third parameter true)
				// and redirect the user to the referring page
				// (the View group page)
				//$this->view->setVariable("expense", $expense, true);
				$this->view->setVariable("errors", $errors, true);

				$this->view->redirect("groups", "view", "id=".$group->getId());
			}
		} 

		// Put the Post object visible to the view
		$this->view->setVariable("expense", $expense);

		// render the view (/view/posts/add.php)
		$this->view->render("expenses", "add");

	}
}
