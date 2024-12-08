<?php
// file: model/ExpenseMapper.php

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
			$this->updateAccumulatedBalance($user->getUserName(), $expense->getGroup()->getId(), -$amount);
        }

        $this->updateAccumulatedBalance($expense->getPayer()->getUserName(), $expense->getGroup()->getId(), $expense->getTotalAmount());
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
		$participants = array();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$participants[$row['member']] = $row['amount'];
		}
	
		return $participants;
	}

	public function update(Expense $expense) {
		$oldExpense = $this->getExpenseDetailsById($expense->getId());
        if ($oldExpense) {
            $this->revertBalanceChanges($oldExpense);
        }
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
		var_dump($expense->getParticipants());
		// Añadir los nuevos participantes y sus importes
		foreach ($expense->getParticipants() as $participant) {
			$user = $participant['user'];
            $amount = $participant['amount'];
            $stmt = $this->db->prepare("INSERT INTO expense_participants(expense, member, amount) VALUES (?, ?, ?)");
            $stmt->execute([$expense->getId(), $user->getUserName(), $amount]);
            $this->updateAccumulatedBalance($user->getUserName(), $expense->getGroup()->getId(), -$amount);
		}

		$this->updateAccumulatedBalance($expense->getPayer()->getUserName(), $expense->getGroup()->getId(), $expense->getTotalAmount());
	}	

	public function delete(Expense $expense) {
		$oldExpense = $this->getExpenseDetailsById($expense->getId());
        if ($oldExpense) {
            $this->revertBalanceChanges($oldExpense);
        }

		$stmt = $this->db->prepare("DELETE FROM expense_participants WHERE expense=?");
		$stmt->execute(array($expense->getId()));

		$stmt = $this->db->prepare("DELETE FROM expenses WHERE expense_id=?");
		$stmt->execute(array($expense->getId()));
	}
	
	private function updateAccumulatedBalance($username, $communityId, $amount) {
        $stmt = $this->db->prepare("UPDATE community_members SET accumulated_balance = accumulated_balance + ? WHERE member = ? AND community = ?");
        $stmt->execute([$amount, $username, $communityId]);
    }

    private function revertBalanceChanges(Expense $expense) {
        $this->updateAccumulatedBalance($expense->getPayer()->getUserName(), $expense->getGroup()->getId(), -$expense->getTotalAmount());
        foreach ($expense->getParticipants() as $participant) {
            $user = $participant['user'];
            $amount = $participant['amount'];
            $this->updateAccumulatedBalance($user->getUserName(), $expense->getGroup()->getId(), $amount);
        }
    }

	
	
}