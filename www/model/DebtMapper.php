<?php
// file: model/DebtMapper.php
require_once(__DIR__."/../config/PDOConnection.php");
require_once(__DIR__."/../model/Debt.php");
require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Group.php");

/**
 * Class DebtMapper
 *
 * Database interface for Debt entities
 */
class DebtMapper {

    /**
     * Reference to the PDO connection
     * @var PDO
     */
    private $db;

    public function __construct() {
        $this->db = PDOConnection::getInstance();
    }

    /**
     * Retrieves all debts associated with a specific group
     *
     * @param int $groupid ID of the group
     * @throws PDOException if a database error occurs
     * @return mixed Array of Debt instances
     */
    public function findDebtsByGroupId($groupid) {
        $stmt = $this->db->prepare("SELECT * FROM debts WHERE community = ?");
        $stmt->execute(array($groupid));
        $debts_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $debts = array();
        foreach ($debts_db as $debt) {
            $debts[] = new Debt(
                $debt["debt_id"],
                new Group($debt["community"]),
                new User($debt["debtor"]),
                new User($debt["creditor"]),
                $debt["amount"]
            );
        }

        return $debts;
    }

    /**
     * Saves a Debt into the database
     *
     * @param Debt $debt The debt to be saved
     * @throws PDOException if a database error occurs
     * @return int The new debt id
     */
    public function save(Debt $debt) {
        $stmt = $this->db->prepare("INSERT INTO debts(community, debtor, creditor, amount) VALUES (?, ?, ?, ?)");
        $stmt->execute(array(
            $debt->getGroup()->getId(),
            $debt->getDebtor()->getUsername(),
            $debt->getCreditor()->getUsername(),
            $debt->getAmount()
        ));

        return $this->db->lastInsertId();
    }

    /**
     * Updates a Debt in the database
     *
     * @param Debt $debt The debt to be updated
     * @throws PDOException if a database error occurs
     * @return void
     */
    public function update(Debt $debt) {
        $stmt = $this->db->prepare("UPDATE debts SET amount=? WHERE debt_id=?");
        $stmt->execute(array(
            $debt->getAmount(),
            $debt->getId()
        ));
    }

    /**
     * Deletes a Debt from the database
     *
     * @param Debt $debt The debt to be deleted
     * @throws PDOException if a database error occurs
     * @return void
     */
    
    public function delete(Debt $debt) {
        $stmt = $this->db->prepare("DELETE FROM debts WHERE debt_id=?");
        $stmt->execute(array($debt->getId()));
    }

    /**
     * Retrieves all debts for a specific user
     *
     * @param User $user The user to find debts for
     * @throws PDOException if a database error occurs
     * @return mixed Array of Debt instances
     */
    public function findDebtsByUser(User $user) {
        $stmt = $this->db->prepare("SELECT * FROM debts WHERE debtor = ? OR creditor = ?");
        $stmt->execute(array($user->getUsername(), $user->getUsername()));
        $debts_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $debts = array();
        foreach ($debts_db as $debt) {
            $debts[] = new Debt(
                $debt["debt_id"],
                new Group($debt["community"]),
                new User($debt["debtor"]),
                new User($debt["creditor"]),
                $debt["amount"]
            );
        }

        return $debts;
    }
}
