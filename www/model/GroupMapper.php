<?php
// file: model/GroupMapper.php
require_once(__DIR__."/../core/PDOConnection.php");

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/Payment.php");

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
	* Note: Payments are not added to the Group instances
	*
	* @throws PDOException if a database error occurs
	* @return mixed Array of Group instances (without payments)
	*/
	public function findAll() {
		$stmt = $this->db->query("SELECT * FROM grupos, usuarios WHERE usuarios.nombre = grupos.administrador");
		$groups_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$groups = array();

		foreach ($groups_db as $group) {
			$admin = new User($group["nombre"]);
			array_push($groups, new Group($group["id"], $group["nombre"], $group["descripcion"], $admin));
		}

		return $groups;
	}

	/**
	* Loads a Group from the database given its id
	*
	* Note: Payments are not added to the Group
	*
	* @throws PDOException if a database error occurs
	* @return Group The Group instances (without payments). NULL
	* if the Group is not found
	*/
	public function findById($groupid){
		$stmt = $this->db->prepare("SELECT * FROM grupos WHERE id=?");
		$stmt->execute(array($groupid));
		$group = $stmt->fetch(PDO::FETCH_ASSOC);

		if($group != null) {
			return new Group(
			$group["id"],
			$group["nombre"],
			$group["descripcion"],
			new User($group["administrador"]));
		} else {
			return NULL;
		}
	}

	/**
	* Loads a Group from the database given its id
	*
	* It includes all the payments
	*
	* @throws PDOException if a database error occurs
	* @return Group The Group instances (without payments). NULL
	* if the Group is not found
	*/
	public function findByIdWithPayments($groupid){
		$stmt = $this->db->prepare("SELECT
			G.id as 'grupos.id',
			G.title as 'grupos.nombre',
			G.content as 'grupos.descripcion',
			G.admin as 'grupos.administrador',
			P.id as 'gastos.id',
			P.content as 'gastos.content',
			P.group as 'gastos.group',
			P.pagador as 'gastos.pagador'

			FROM grupos G LEFT OUTER JOIN payments P
			ON G.id = P.group
			WHERE
			G.id=? ");

			$stmt->execute(array($groupid));
			$group_wt_payments= $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (sizeof($group_wt_payments) > 0) {
				$group = new Group($group_wt_payments[0]["grupos.id"],
				$group_wt_payments[0]["grupos.nombre"],
				$group_wt_payments[0]["grupos.descripcion"],
				new User($group_wt_payments[0]["grupos.administrador"]));
				$payments_array = array();
				if ($group_wt_payments[0]["gastost.id"]!=null) {
					foreach ($group_wt_payments as $payment){
						$payment = new Payment( $payment["gastos.id"],
						$payment["gastos.content"],
						new User($payment["gastos.author"]),
						$group);
						array_push($payments_array, $payment);
					}
				}
				$group->setPayments($payments_array);

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
			$stmt = $this->db->prepare("INSERT INTO grupos(nombre, descripcion, admin) values (?,?,?)");
			$stmt->execute(array($group->getTitle(), $group->getContent(), $group->getAuthor()->getUsername()));
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
			$stmt = $this->db->prepare("UPDATE grupos set nombre=?, descripcion=? where administrador=?");
			$stmt->execute(array($group->getTitle(), $group->getContent(), $group->getId()));
		}

		/**
		* Deletes a Group into the database
		*
		* @param Group $group The group to be deleted
		* @throws PDOException if a database error occurs
		* @return void
		*/
		public function delete(Group $group) {
			$stmt = $this->db->prepare("DELETE from grupos WHERE id=?");
			$stmt->execute(array($group->getId()));
		}

	}
