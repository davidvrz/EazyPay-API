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

			foreach ($participants as $username => $amount) {
				$user = $this->userMapper->getUser($username);
				if ($user && floatval($amount) > 0) { // Solo agregar si el monto es mayor a 0
					// Agregar el participante con la cantidad específica
					$expense->addParticipant($user, floatval($amount));
				} elseif (!$user) {
					// Manejar el error si no se encuentra el usuario
					$errors[] = "User $username not found";
				} elseif (floatval($amount) <= 0) {
					// Error si el monto es menor o igual a 0
					$errors['participants'][$username] = "Amount for $username must be greater than 0";
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

		// Set the expense to be accessible in the view
		$this->view->setVariable("expense", $expense);

		// Render the expense view page
		$this->view->render("expenses", "view");
	}


	public function edit() {
		// Verificar que se haya enviado el id del gasto
		if (!isset($_GET["id"])) {
			throw new Exception("Expense ID is mandatory");
		}
	
		$expenseId = $_GET["id"];
		
		// Recuperar el gasto de la base de datos
		$expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
	
		if ($expense == NULL) {
			throw new Exception("Expense not found");
		}
	
		// Verificar si el usuario tiene permisos para editar este gasto (puede ser el pagador)
		if ($expense->getPayer()->getUsername() !== $this->currentUser->getUsername()) {
			throw new Exception("You do not have permission to edit this expense");
		}
	
		// Recuperar el ID del grupo relacionado con el gasto
		$groupId = $expense->getGroup()->getId();  // Asumiendo que tienes un método getGroupId en la clase Expense
	
		// Recuperar el grupo de la base de datos si es necesario (por ejemplo, si lo necesitas completo)
		$group = $this->groupMapper->getGroupDetailsById($groupId);
	
		if ($group == NULL) {
			throw new Exception("Group not found");
		}
	
		// Si se recibe una solicitud POST con datos del formulario, proceder a actualizar
		if ($_SERVER["REQUEST_METHOD"] === "POST") {
			$description = trim($_POST["description"]);
			$totalAmount = trim($_POST["totalAmount"]);
			$participants = $_POST["participants"]; // Un array de usuarios y montos
	
			$errors = [];
	
			// Comprobamos que los participantes estén correctamente definidos
			foreach ($participants as $username => $amount) {
				$user = $this->userMapper->getUser($username);
				if (!$user) {
					// Manejar el error si no se encuentra el usuario
					$errors[] = "User $username not found";
				} elseif (floatval($amount) <= 0) {
					// Error si el monto es menor o igual a 0
					$errors['participants'][$username] = "Amount for $username must be greater than 0";
				}
			}
	
			// Asegurarnos de que los datos del gasto sean válidos
			if (empty($description)) {
				$errors[] = "Description cannot be empty";
			}
			if (empty($totalAmount) || !is_numeric($totalAmount) || $totalAmount <= 0) {
				$errors[] = "Total amount must be a positive number";
			}
	
			// Validar los participantes
			$validParticipants = [];
			foreach ($participants as $username => $amount) {
				if (floatval($amount) > 0) {
					$validParticipants[] = [
						'user' => $this->userMapper->getUser($username), 
						'amount' => floatval($amount)
					];
				}
			}
	
			if (empty($errors)) {
				// Actualizar los valores del gasto
				$expense->setDescription($description);
				$expense->setTotalAmount($totalAmount);

				// Actualizar la lista de participantes correctamente
				$expense->setParticipants($validParticipants); 

				// Guardar el gasto actualizado en la base de datos
				$this->expenseMapper->update($expense);

				// Redirigir a la vista del gasto editado
				header("Location: index.php?controller=expenses&action=view&id=" . $expense->getId());
				exit();
			}
			
	
			// Si hay errores, asignarlos a la vista
			$this->view->setVariable("errors", $errors);
		}
	
		// Pasar el gasto y el grupo a la vista
		$this->view->setVariable("expense", $expense);
		$this->view->setVariable("group", $group);  // Pasa el grupo a la vista
	
		// Mostrar el formulario de edición
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
		if ($group->getAdmin() != $this->currentUser) {
			throw new Exception("User is not authorized to delete this expense");
		}
	
		// Eliminamos el gasto de la base de datos
		$this->expenseMapper->delete($expense);
	
		// Enviamos un mensaje flash y redirigimos al usuario a la vista de gastos del grupo
		$this->view->setFlash(sprintf(i18n("Expense \"%s\" successfully deleted."), $expense->getDescription()));
		
		// Realizamos la redirección
		$this->view->redirect("groups", "view", "id=".$group->getId());
	}
	
	
}
