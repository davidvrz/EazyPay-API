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
	public function findAll() {
		$stmt = $this->db->query("SELECT community_id, community_name, community_description, admin FROM communities");
		$groups_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$groups = array();

		foreach ($groups_db as $group) {
			$admin = new User($group["admin"]);
			array_push($groups, new Group($group["community_id"], $group["community_name"], $group["community_description"], $admin));
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
		$stmt = $this->db->prepare("SELECT * FROM communities WHERE community_id=?");
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
	
			// Obtener los gastos del grupo utilizando el método getExpensesByGroupId
			$expenses = $this->getExpensesByGroupId($groupid);
			$group->setExpenses($expenses);
	
			// Obtener los miembros del grupo utilizando el método getMembersByGroupId
			$members = $this->getMembersByGroupId($groupid);

			// Asegúrate de que el creador esté en la lista de miembros
			$creator = new User($group_db["admin"]);
			array_unshift($members, $creator); // Añadir al creador al principio de la lista	

			$group->setMembers($members);
	
			// Devolver el grupo con todos los detalles
			return $group;
		} else {
			return NULL; // Si no existe el grupo
		}
	}

	private function getExpensesByGroupId($groupid) {
		// Obtener todos los gastos asociados a este grupo
		$stmt = $this->db->prepare("SELECT * FROM expenses WHERE community = ?");
		$stmt->execute(array($groupid));
		$expenses_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$expenses = array();
		foreach ($expenses_db as $expense) {
			// Crear y agregar los gastos a la lista (suponiendo que la clase Expense existe)
			$expenses[] = new Expense(
				$expense["expense_id"],
				new Group($expense["community"]), // Asignamos el grupo correspondiente
				$expense["expense_description"],
				$expense["total_amount"],
				new User($expense["payer"]) // Payer es un usuario
        );		
	}
	
		return $expenses;
	}

	private function getMembersByGroupId($groupid) {
		// Obtener todos los miembros del grupo, excepto el administrador
		$stmt = $this->db->prepare("SELECT member FROM community_members WHERE community = ?");
		$stmt->execute(array($groupid));
		$members_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$members = array();
		foreach ($members_db as $member) {
			// Suponiendo que 'user_id' hace referencia a los usuarios
			$members[] = new User($member["member"]);
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
		$stmt->execute(array($group->getName(), $group->getDescription(), $group->getAdmin()->getUserName()));
	
		// Obtener el ID del nuevo grupo insertado
		$groupId = $this->db->lastInsertId();

		// Guardar al creador como miembro automáticamente
		$creator = $group->getAdmin(); // El creador es el administrador del grupo
    	$stmt = $this->db->prepare("INSERT INTO community_members(community, member) VALUES (?, ?)");
    	$stmt->execute(array($groupId, $creator->getUserName()));
	
		// Guardar los miembros del grupo
		foreach ($group->getMembers() as $member) {
			// Verificar si el miembro ya está en la base de datos
			$stmt = $this->db->prepare("SELECT COUNT(*) FROM community_members WHERE community = ? AND member = ?");
			$stmt->execute(array($groupId, $member->getUserName()));
			$exists = $stmt->fetchColumn();
	
			// Si el miembro no existe, agregarlo
			if ($exists == 0) {
				$stmt = $this->db->prepare("INSERT INTO community_members(community, member) VALUES (?, ?)");
				$stmt->execute(array($groupId, $member->getUserName()));
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
				$expense->getPayer()->getUserName()
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
		$stmt->execute(array($group->getId(), $group->getAdmin()->getUserName())); // Excluir al creador
	
		foreach ($group->getMembers() as $member) {
			// Verificar si el miembro ya está en la base de datos
			$stmt = $this->db->prepare("SELECT COUNT(*) FROM community_members WHERE community = ? AND member = ?");
			$stmt->execute(array($group->getId(), $member->getUserName()));
			$exists = $stmt->fetchColumn();
	
			// Si el miembro no existe, agregarlo
			if ($exists == 0) {
				$stmt = $this->db->prepare("INSERT INTO community_members(community, member) VALUES (?, ?)");
				$stmt->execute(array($group->getId(), $member->getUserName()));
			}
		}
	
		// Eliminar los gastos actuales y añadir los nuevos
		$stmt = $this->db->prepare("DELETE FROM expenses WHERE community=?");
		$stmt->execute(array($group->getId()));
	
		foreach ($group->getExpenses() as $expense) {
			$stmt = $this->db->prepare("INSERT INTO expenses(community, expense_description, total_amount, date, payer) VALUES (?, ?, ?, ?, ?)");
			$stmt->execute(array(
				$group->getId(),
				$expense->getDescription(),
				$expense->getTotalAmount(),
				$expense->getDate(),
				$expense->getPayer()->getUserName()
			));
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
		// Eliminar los miembros del grupo
		$stmt = $this->db->prepare("DELETE FROM community_members WHERE community=?");
		$stmt->execute(array($group->getId()));
	
		// Eliminar los gastos del grupo
		$stmt = $this->db->prepare("DELETE FROM expenses WHERE community=?");
		$stmt->execute(array($group->getId()));
	
		// Eliminar el grupo
		$stmt = $this->db->prepare("DELETE FROM communities WHERE community_id=?");
		$stmt->execute(array($group->getId()));
	}

}
