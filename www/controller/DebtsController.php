<?php
// file: controller/DebtsController.php

require_once(__DIR__."/../model/Debt.php");
require_once(__DIR__."/../model/DebtMapper.php");
require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");

require_once(__DIR__."/../config/ViewManager.php");
require_once(__DIR__."/../controller/BaseController.php");

/**
 * Class DebtsController
 *
 * Controller to manage CRUDL operations for Debt entities
 *
 */
class DebtsController extends BaseController {

    /**
     * Reference to the DebtMapper to interact with the database
     *
     * @var DebtMapper
     */
    private $debtMapper;
    private $userMapper;

    public function __construct() {
        parent::__construct();

        $this->debtMapper = new DebtMapper();
        $this->userMapper = new UserMapper();
    }

    /**
     * Action to list all debts
     *
     * Loads all the debts from the database.
     *
     */
    public function index() {
        $debts = $this->debtMapper->findAll();
        $this->view->setVariable("debts", $debts);
        $this->view->render("debts", "index");
    }

    /**
     * Action to view details of a specific debt
     *
     * The expected HTTP parameters are:
     * - id: Id of the debt (via HTTP GET)
     */
    public function view() {
        if (!isset($_GET["id"])) {
            throw new Exception("id is mandatory");
        }

        $debtId = $_GET["id"];
        $debt = $this->debtMapper->findById($debtId);

        if ($debt == NULL) {
            throw new Exception("no such debt with id: ".$debtId);
        }

        $this->view->setVariable("debt", $debt);
        $this->view->render("debts", "view");
    }

    /**
     * Action to add a new debt
     *
     * The expected HTTP parameters are:
     * - amount: Amount of the debt (via HTTP POST)
     * - debtor: Debtor of the debt (via HTTP POST)
     * - creditor: Creditor of the debt (via HTTP POST)
     */
    public function add() {
        if (!isset($this->currentUser)) {
            throw new Exception("Adding debts requires login");
        }

        $debt = new Debt();

        if (isset($_POST["submit"])) { 
            $debt->setAmount($_POST["amount"]);
            $debt->setDebtor($_POST["debtor"]);
            $debt->setCreditor($_POST["creditor"]);

            try {
                $debt->checkIsValidForCreate();
                $this->debtMapper->save($debt);
                $this->view->setFlash(sprintf(i18n("Debt successfully added.")));
                $this->view->redirect("debts", "index");

            } catch (ValidationException $ex) {
                $errors = $ex->getErrors();
                $this->view->setVariable("errors", $errors);
            }
        }

        $this->view->setVariable("debt", $debt);
        $this->view->render("debts", "add");
    }

    /**
     * Action to edit an existing debt
     *
     * The expected HTTP parameters are:
     * - id: Id of the debt (via HTTP POST and GET)
     * - amount: Updated amount of the debt (via HTTP POST)
     * - debtor: Updated debtor (via HTTP POST)
     * - creditor: Updated creditor (via HTTP POST)
     */
    public function edit() {
        if (!isset($_REQUEST["id"])) {
            throw new Exception("A debt id is mandatory");
        }

        if (!isset($this->currentUser)) {
            throw new Exception("Editing debts requires login");
        }

        $debtId = $_REQUEST["id"];
        $debt = $this->debtMapper->findById($debtId);

        if ($debt == NULL) {
            throw new Exception("no such debt with id: ".$debtId);
        }

        if (isset($_POST["submit"])) {
            $debt->setAmount($_POST["amount"]);
            $debt->setDebtor($_POST["debtor"]);
            $debt->setCreditor($_POST["creditor"]);

            try {
                $debt->checkIsValidForUpdate();
                $this->debtMapper->update($debt);
                $this->view->setFlash(sprintf(i18n("Debt successfully updated.")));
                $this->view->redirect("debts", "index");

            } catch (ValidationException $ex) {
                $errors = $ex->getErrors();
                $this->view->setVariable("errors", $errors);
            }
        }

        $this->view->setVariable("debt", $debt);
        $this->view->render("debts", "edit");
    }

    /**
     * Action to delete a debt
     *
     * The expected HTTP parameters are:
     * - id: Id of the debt (via HTTP POST)
     */
    public function delete() {
        if (!isset($_POST["id"])) {
            throw new Exception("id is mandatory");
        }

        if (!isset($this->currentUser)) {
            throw new Exception("Deleting debts requires login");
        }

        $debtId = $_POST["id"];
        $debt = $this->debtMapper->findById($debtId);

        if ($debt == NULL) {
            throw new Exception("no such debt with id: ".$debtId);
        }

        $this->debtMapper->delete($debt);
        $this->view->setFlash(sprintf(i18n("Debt successfully deleted.")));
        $this->view->redirect("debts", "index");
    }
}
?>
