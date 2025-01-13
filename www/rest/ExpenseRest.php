<?php
// file: rest/ExpenseRest.php

require_once(__DIR__."/../model/Expense.php");
require_once(__DIR__."/../model/ExpenseMapper.php");

require_once(__DIR__."/../model/GroupMapper.php");
require_once(__DIR__."/../model/UserMapper.php");

require_once(__DIR__."/../rest/BaseRest.php");

class ExpenseRest extends BaseRest {
    private $expenseMapper;
    private $groupMapper;
    private $userMapper;

    public function __construct() {
        parent::__construct();
        $this->expenseMapper = new ExpenseMapper();
        $this->groupMapper = new GroupMapper();
        $this->userMapper = new UserMapper();
    }

    public function getExpense($groupId, $expenseId) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->findById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);            
            return;
        }

        if (!($this->groupMapper->isGroupMember($currentUser->getUsername(), $groupId))) {
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-not-a-group-member"]);
            return;
        }

        $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
        if (!$expense) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-found"]);
            return;
        }
    
        if ($expense->getGroup()->getId() != $groupId) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-belong-to-group"]);
            return;
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
        header('Content-Type: application/json');
        echo json_encode(["data" => array(
            "id" => $expense->getId(),
            "group" => $expense->getGroup()->getId(),
            "description" => $expense->getDescription(),
            "totalAmount" => $expense->getTotalAmount(),
            "payer" => $expense->getPayer()->getUsername(),
            "participants" => array_map(function ($username, $amount) {
            return array(
                "username" => $username,
                "amount" => $amount
            );
        }, array_keys($expense->getParticipants()), $expense->getParticipants())
        )]);
    }


    // Método para añadir un nuevo gasto
    public function addExpense($groupId, $data) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->findById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);            
            return;
        }

        if (!($this->groupMapper->isGroupMember($currentUser->getUsername(), $groupId))) {
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-not-a-group-member"]);
            return;
        }
    
        $expense = new Expense();

        if (isset($data->description)) {
            $expense->setDescription($data->description);
        }

        if (isset($data->totalAmount)){
            $expense->setTotalAmount($data->totalAmount);
        }

        if (isset($data->payer)){
            $expense->setPayer($this->userMapper->getUser($data->payer));
        }

        $expense->setGroup($group);

        if (isset($data->participants)) {
            foreach ($data->participants as $username => $amount) {
                $user = $this->userMapper->getUser($username);
                if ($this->groupMapper->isGroupMember($user->getUsername(), $groupId) && floatval($amount) > 0) {
                    $expense->addParticipant($user, round(floatval($amount), 2));
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    header('Content-Type: application/json');
                    echo(json_encode(["errors" => "error-expense-invalid-participant-or-amount" . $username]));
                    return;
                }
            }
        }

        try {
            $expense->checkIsValidForCreate();
            $this->expenseMapper->save($expense);

            // Respuesta exitosa
            header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created');
            header('Location: ' . $_SERVER['REQUEST_URI'] . "/" . $expense->getId());
            header('Content-Type: application/json');
            echo json_encode(["data" => array(
                "id" => $expense->getId(),
                "group" =>  $expense->getGroup()->getId(),
                "description" => $expense->getDescription(),
                "totalAmount" => $expense->getTotalAmount(),
                "payer" => $expense->getPayer()->getUsername(),
                "participants" => array_map(function ($user, $amount) {
                    return [
                        "username" => $user,
                        "amount" => $amount
                    ];
                }, array_keys($expense->getParticipants()), $expense->getParticipants())
            )]);
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(["errors" => $e->getErrors()]);
        }
    }

    // Método para actualizar un gasto
    public function updateExpense($groupId, $expenseId, $data) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->findById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);            
            return;
        }

        $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
        if (!$expense) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-found"]);
            return;
        }

        if (!($expense->getPayer() == $currentUser || $group->getAdmin() == $currentUser)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-unauthorized-update-expense"]);
            return;
        }

        if ($expense->getGroup()->getId() != $groupId) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-belong-to-group"]);
            return;
        }

        if (isset($data->description)) {
            $expense->setDescription($data->description);
        }
        if (isset($data->totalAmount)) {
            $expense->setTotalAmount($data->totalAmount);
        }
        if (isset($data->payer)){
            $expense->setPayer($this->userMapper->getUser($data->payer));
        }
        
        if (isset($data->participants)) {
            $expense->clearParticipants();
            foreach ($data->participants as $username => $amount) {
                $user = $this->userMapper->getUser($username);
                if ($user && floatval($amount) > 0) {
                    $expense->addParticipant($user, round(floatval($amount), 2));
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    header('Content-Type: application/json');
                    echo(json_encode(["errors" => "error-expense-invalid-participant-or-amount". $username]));
                    return;
                }
            }
        }

        try {
            $expense->checkIsValidForUpdate();
            $this->expenseMapper->update($expense);

            header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
            header('Content-Type: application/json');
            echo json_encode(["data" => array(
                "id" => $expense->getId(),
                "group" =>  $expense->getGroup()->getId(),
                "description" => $expense->getDescription(),
                "totalAmount" => $expense->getTotalAmount(),
                "payer" => $expense->getPayer()->getUsername(),
                "participants" => array_map(function ($user, $amount) {
                    return [
                        "username" => $user,
                        "amount" => $amount
                    ];
                }, array_keys($expense->getParticipants()), $expense->getParticipants())
            )]);
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(["errors" => $e->getErrors()]);
        }
    }

    // Método para eliminar un gasto
    public function deleteExpense($groupId, $expenseId) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->findById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);            
            return;
        }

        $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
        if (!$expense) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-found"]);            
            return;
        }

        if ($expense->getGroup()->getId() != $groupId) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-expense-not-belong-to-group"]);
            return;
        }

        if (!($expense->getPayer() == $currentUser || $group->getAdmin()  == $currentUser)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-unauthorized-delete-expense"]);
            return;
        }

        $this->expenseMapper->delete($expense);

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
        header('Content-Type: application/json');
        echo(json_encode(["message" => "expense-delete-successfully"]));
    }
}

// URI-MAPPING for this Rest endpoint
$expenseRest = new ExpenseRest();
URIDispatcher::getInstance()
    ->map("GET", "/group/$1/expense/$2", array($expenseRest, "getExpense"))        
    ->map("POST", "/group/$1/expense", array($expenseRest, "addExpense"))
    ->map("PUT", "/group/$1/expense/$2", array($expenseRest, "updateExpense"))     
    ->map("DELETE", "/group/$1/expense/$2", array($expenseRest, "deleteExpense"));
