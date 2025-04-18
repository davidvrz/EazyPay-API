<?php
// file: model/GroupMapper.php
require_once(__DIR__."/../config/PDOConnection.php");

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/Expense.php");

/**
* Class GroupMapper
*
* Database interface for Group entities
*
* @author lipido <lipido@gmail.com>
*/
class GroupMapper {

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Retrieves all groups
	*
	* Note: Expenses are not added to the Group instances
	*
	* @throws PDOException if a database error occurs
	* @return mixed Array of Group instances (without expenses)
	*/
	public function findAll($username) {
		$stmt = $this->db->prepare("SELECT community FROM community_members WHERE member = ?");
		$stmt->execute(array($username));
		$groups_db = $stmt->fetchAll(PDO::FETCH_COLUMN);
	
		$groups = array();
	
		foreach ($groups_db as $group_id) {
			$group = $this->findById($group_id);
			if ($group !== null) {
				$groups[] = $group;
			}
		}
	
		return $groups;
	}
	

	/**
	* Loads a Group from the database given its id
	*
	* Note: Expenses are not added to the Group
	*
	* @throws PDOException if a database error occurs
	* @return Group The Group instances (without expenses). NULL
	* if the Group is not found
	*/
	public function findById($groupid){
		$stmt = $this->db->prepare("SELECT * FROM communities WHERE community_id = ?");
		$stmt->execute(array($groupid));
		$group = $stmt->fetch(PDO::FETCH_ASSOC);

		if($group != null) {
			return new Group(
			$group["community_id"],
			$group["community_name"],
			$group["community_description"],
			new User($group["admin"]));
		} else {
			return NULL;
		}
	}

	
	public function getGroupDetailsById($groupid) {
		// Obtener los detalles básicos del grupo
		$stmt = $this->db->prepare("SELECT * FROM communities WHERE community_id = ?");
		$stmt->execute(array($groupid));
		$group_db = $stmt->fetch(PDO::FETCH_ASSOC);
	
		if ($group_db != null) {
			// Crear el objeto Group con la información básica
			$group = new Group(
				$group_db["community_id"],
				$group_db["community_name"],
				$group_db["community_description"],
				new User($group_db["admin"]) // Administrador del grupo
			);
	
			$expenses = $this->getExpensesByGroupId($groupid);
			$group->setExpenses($expenses);
	
			$members = $this->getMembersWithBalanceByGroupId($groupid);
			$group->setMembers($members);
	
			return $group;
		} else {
			return null; 
		}
	}

	private function getExpensesByGroupId($groupid) {
		$stmt = $this->db->prepare("SELECT * FROM expenses WHERE community = ?");
		$stmt->execute(array($groupid));
		$expenses_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$expenses = array();
		foreach ($expenses_db as $expense) {
			$expenses[] = new Expense(
				$expense["expense_id"],
				new Group($expense["community"]), 
				$expense["expense_description"],
				$expense["total_amount"],
				new User($expense["payer"]) 
        	);		
		}
	
		return $expenses;
	}

	private function getMembersWithBalanceByGroupId($groupid) {
		// Obtener todos los miembros del grupo con sus balances
		$stmt = $this->db->prepare("SELECT * FROM community_members WHERE community = ?");
		$stmt->execute(array($groupid));
		$members = array();
	
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$members[$row['member']] = $row['accumulated_balance'];
		}
	
		return $members;
	}

	/**
	* Saves a Group into the database
	*
	* @param Group $group The group to be saved
	* @throws PDOException if a database error occurs
	* @return int The mew group id
	*/
	public function save(Group $group) {
		
		// Insertar el grupo en la base de datos
		$stmt = $this->db->prepare("INSERT INTO communities(community_name, community_description, admin) VALUES (?,?,?)");
		$stmt->execute(array($group->getName(), $group->getDescription(), $group->getAdmin()->getUsername()));
	
		// Obtener el ID del nuevo grupo insertado
		$groupId = $this->db->lastInsertId();

		// Guardar al creador como miembro automáticamente
		$creator = $group->getAdmin(); // El creador es el administrador del grupo
    	$stmt = $this->db->prepare("INSERT INTO community_members(community, member) VALUES (?, ?)");
    	$stmt->execute(array($groupId, $creator->getUsername()));
	
		// Guardar los miembros del grupo
		foreach ($group->getMembers() as $user => $balance) {
			$stmt = $this->db->prepare("SELECT COUNT(*) FROM community_members WHERE community = ? AND member = ?");
			$stmt->execute(array($groupId, $user));
			$exists = $stmt->fetchColumn();
	
			// Si el miembro no existe, agregarlo
			if ($exists == 0) {
				$stmt = $this->db->prepare("INSERT INTO community_members(community, member, accumulated_balance) VALUES (?, ?, ?)");
				$stmt->execute(array($groupId, $user, $balance));
			}
		}
	
		// Guardar los gastos del grupo
		foreach ($group->getExpenses() as $expense) {
			$stmt = $this->db->prepare("INSERT INTO expenses(community, expense_description, total_amount, date, payer) VALUES (?, ?, ?, ?, ?)");
			$stmt->execute(array(
				$groupId,
				$expense->getDescription(),
				$expense->getTotalAmount(),
				$expense->getDate(),
				$expense->getPayer()->getUsername()
			));
		}
		
		return $groupId;
	}

	/**
	* Updates a Group in the database
	*
	* @param Group $group The group to be updated
	* @throws PDOException if a database error occurs
	* @return void
	*/
	public function update(Group $group) {
		// Actualizar la información básica del grupo
		$stmt = $this->db->prepare("UPDATE communities SET community_name=?, community_description=? WHERE community_id=?");
		$stmt->execute(array($group->getName(), $group->getDescription(), $group->getId()));
	
		// Eliminar los miembros actuales y añadir los nuevos, pero no eliminar al creador
		$stmt = $this->db->prepare("DELETE FROM community_members WHERE community=? AND member != ?");
		$stmt->execute(array($group->getId(), $group->getAdmin()->getUsername())); // Excluir al creador
	
		foreach ($group->getMembers() as $user => $balance) {
			// Verificar si el miembro ya está en la base de datos
			$stmt = $this->db->prepare("SELECT COUNT(*) FROM community_members WHERE community = ? AND member = ?");
			$stmt->execute(array($group->getId(), $user));
			$exists = $stmt->fetchColumn();
	
			// Si el miembro no existe, agregarlo
			if ($exists == 0) {
				$stmt = $this->db->prepare("INSERT INTO community_members(community, member) VALUES (?, ?)");
				$stmt->execute(array($group->getId(), $user));
			}
		}
	}

	/**
	* Deletes a Group into the database
	*
	* @param Group $group The group to be deleted
	* @throws PDOException if a database error occurs
	* @return void
	*/
	public function delete(Group $group) {
		$stmt = $this->db->prepare("DELETE FROM community_members WHERE community=?");
		$stmt->execute(array($group->getId()));
	
		$stmt = $this->db->prepare("DELETE FROM expenses WHERE community=?");
		$stmt->execute(array($group->getId()));
	
		$stmt = $this->db->prepare("DELETE FROM communities WHERE community_id=?");
		$stmt->execute(array($group->getId()));
	}

	public function isGroupMember($username, $groupId) {
		$stmt = $this->db->prepare("SELECT COUNT(*) FROM community_members WHERE member = ? AND community = ?");
		$stmt->execute([$username, $groupId]);
		return $stmt->fetchColumn() > 0;
	}
	

}
