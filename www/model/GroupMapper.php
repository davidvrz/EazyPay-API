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
		$stmt = $this->db->query("SELECT * FROM communities, users WHERE users.username = communities.admin");
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
		$stmt = $this->db->prepare("SELECT * FROM communities WHERE id=?");
		$stmt->execute(array($groupid));
		$group = $stmt->fetch(PDO::FETCH_ASSOC);

		if($group != null) {
			return new Group(
			$group["id"],
			$group["name"],
			$group["description"],
			new User($group["admin"]));
		} else {
			return NULL;
		}
	}

	/**
	* Loads a Group from the database given its id
	*
	* It includes all the expenses
	*
	* @throws PDOException if a database error occurs
	* @return Group The Group instances (without expenses). NULL
	* if the Group is not found
	*/
	public function findByIdWithExpenses($groupid){
		$stmt = $this->db->prepare("SELECT
			C.community_id AS community_id,
            C.community_name AS community_name,
            C.community_description AS community_description,
            C.admin AS admin,
            E.expense_id AS expense_id,
			E.community AS community,
            E.expense_description AS expense_description,
            E.total_amount AS total_amount,
            E.date AS expense_date,
            E.payer AS payer

			FROM communities C LEFT OUTER JOIN expenses E
			ON C.community_id = E.community
			WHERE
			C.community_id=? ");

			$stmt->execute(array($groupid));
			$group_wt_expenses= $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (sizeof($group_wt_expenses) > 0) {
				$group = new Group($group_wt_expenses[0]["community_id"],
				$group_wt_expenses[0]["community_name"],
				$group_wt_expenses[0]["community_description"],
				new User($group_wt_expenses[0]["admin"]));
				$expenses_array = array();
				if ($group_wt_expenses[0]["expense_id"]!=null) {
					foreach ($group_wt_expenses as $expense){
						$expense = new Expense( $expense["expense_id"],
						$group,
						$expense["expense_description"],
						$expense["total_amount"],
						new User($expense["payer"]));
						array_push($expenses_array, $expense);
					}
				}
				$group->setExpenses($expenses_array);

				return $group;
			}else {
				return NULL;
			}
		}

		/**
		* Saves a Group into the database
		*
		* @param Group $group The group to be saved
		* @throws PDOException if a database error occurs
		* @return int The mew group id
		*/
		public function save(Group $group) {
			$stmt = $this->db->prepare("INSERT INTO communities(community_name, community_description, admin) values (?,?,?)");
			$stmt->execute(array($group->getName(), $group->getDescription(), $group->getAdmin()->getUsername()));
			return $this->db->lastInsertId();
		}

		/**
		* Updates a Group in the database
		*
		* @param Group $group The group to be updated
		* @throws PDOException if a database error occurs
		* @return void
		*/
		public function update(Group $group) {
			$stmt = $this->db->prepare("UPDATE communities set community_name=?, community_description=? where administrador=?");
			$stmt->execute(array($group->getName(), $group->getDescription(), $group->getId()));
		}

		/**
		* Deletes a Group into the database
		*
		* @param Group $group The group to be deleted
		* @throws PDOException if a database error occurs
		* @return void
		*/
		public function delete(Group $group) {
			$stmt = $this->db->prepare("DELETE from communities WHERE id=?");
			$stmt->execute(array($group->getId()));
		}

	}
