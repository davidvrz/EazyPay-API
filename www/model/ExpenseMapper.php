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
}