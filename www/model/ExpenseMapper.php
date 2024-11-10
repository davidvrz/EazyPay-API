<?php
// file: model/escriptionMapper.php

require_once(__DIR__."/../config/PDOConnection.php");

require_once(__DIR__."/../model/Expense.php");

/**
* Class ExpenseMapper
*
* Database interface for Expense entities
*
* @author lipido <lipido@gmail.com>
*/
class ExpenseMapper {

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Saves a expense
	*
	* @param Expense $expense The expense to save
	* @throws PDOException if a database error occurs
	* @return int The new expense id
	*/
	public function save(Expense $expense) {
		$stmt = $this->db->prepare("INSERT INTO expenses(community, expense_description, total_amount, payer) values (?,?,?,?)");
		$stmt->execute(array($expense->getGroup()->getId(), $expense->getDescription(), $expense->getTotalAmount(), $expense->getPayer()->getUserName()));
		$expenseId = $this->db->lastInsertId();

		foreach ($expense->getParticipants() as $participant) {
            $user = $participant['user'];
            $amount = $participant['amount'];
            $stmt = $this->db->prepare("INSERT INTO expense_participants(expense, member, amount) VALUES (?,?,?)");
            $stmt->execute([$expenseId, $user->getUserName(), $amount]);
        }

		return $expenseId;
	}

	public function getExpenseDetailsById($expenseid) {
		// Obtener los detalles básicos del gasto
		$stmt = $this->db->prepare("SELECT * FROM expenses WHERE expense_id = ?");
		$stmt->execute(array($expenseid));
		$expense_db = $stmt->fetch(PDO::FETCH_ASSOC);
	
		if ($expense_db != null) {
			// Crear el objeto Expense con la información básica
			$expense = new Expense(
				$expense_db["expense_id"],
				new Group($expense_db["community"]), // Asignamos el grupo correspondiente
				$expense_db["expense_description"],
				$expense_db["total_amount"],
				new User($expense_db["payer"]) // Payer es un usuario
			);
	
			// Obtener los participantes del gasto utilizando el método getParticipantsByExpenseId
			$participants = $this->getParticipantsByExpenseId($expenseid);
			$expense->setParticipants($participants);
	
			// Devolver el gasto con todos los detalles
			return $expense;
		} else {
			return NULL; // Si no existe el gasto
		}
	}
	
	private function getParticipantsByExpenseId($expenseid) {
		// Obtener todos los participantes del gasto (esto incluirá las contribuciones)
		$stmt = $this->db->prepare("SELECT * FROM expense_participants WHERE expense = ?");
		$stmt->execute(array($expenseid));
		$participants_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$participants = array();
		foreach ($participants_db as $participant) {
			$user = new User($participant["member"]);
			$participants[$user->getUsername()] = $participant["amount"];
		}
	
		return $participants;
	}

	public function updateExpense(Expense $expense) {
		// Actualizar la información básica del gasto (descripción y monto total)
		$stmt = $this->db->prepare("UPDATE expenses SET expense_description=?, total_amount=?, payer=? WHERE expense_id=?");
		$stmt->execute(array(
			$expense->getDescription(),
			$expense->getTotalAmount(),
			$expense->getPayer()->getUserName(),
			$expense->getId()
		));
	
		// Eliminar los participantes actuales del gasto (con sus importes)
		$stmt = $this->db->prepare("DELETE FROM expense_participants WHERE expense=?");
		$stmt->execute(array($expense->getId()));
	
		// Añadir los nuevos participantes y sus importes
		foreach ($expense->getParticipants() as $participant => $amount) {
			$stmt = $this->db->prepare("INSERT INTO expense_participants(expense, member, amount) VALUES (?, ?, ?)");
			$stmt->execute(array($expense->getId(), $participant, $amount));
		}
	}	
	
	
}