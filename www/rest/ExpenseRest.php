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

    public function addExpense($groupId, $data) {        
        //$currentUser = $this->authenticateUser();
        if (!isset($groupId)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            echo(json_encode(["error" => "Group ID is required"]));
            return;
        }
 
        $group = $this->groupMapper->findById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            echo(json_encode(["error" => "Group not found"]));
            return;
        }
        
        if (!isset($data->description) || !isset($data->totalAmount) || !isset($data->payer)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            echo(json_encode(["error" => "Description, total amount, and payer are required"]));
            return;
        }

        $payer = $this->userMapper->getUser($data->payer);
        if (!$payer) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            echo(json_encode(["error" => "Payer not found"]));
            return;
        }

        $expense = new Expense();

        $expense->setDescription($data->description);
        $expense->setTotalAmount($data->totalAmount);
        $expense->setGroup($group);
        $expense->setPayer($payer);

        if (!isset($data->participants)) {
            echo(json_encode($data->participants));
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            echo(json_encode(["error" => "Participants must be a valid object with usernames and amounts"]));
            return;
        }

        foreach ($data->participants as $username => $amount) {
            $user = $this->userMapper->getUser($username);
            if ($user && floatval($amount) > 0) {
                $expense->addParticipant($user, round(floatval($amount), 2));
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                echo(json_encode(["error" => "Invalid participant or amount: $username"]));
                return;
            }
        }

        try {
            //$expense->checkIsValidForCreate();
            $this->expenseMapper->save($expense);

            // Respuesta exitosa
            header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created');
            header('Location: ' . $_SERVER['REQUEST_URI'] . "/" . $expense->getId());
            header('Content-Type: application/json');
            echo(json_encode([
                "id" => $expense->getId(),
                "description" => $expense->getDescription(),
                "totalAmount" => $expense->getTotalAmount(),
                "payer" => $expense->getPayer()->getUsername(),
                "group" =>  $expense->getGroup()->getId(),
                "participants" => array_map(function ($user, $amount) {
                    return [
                        $user,
                        "username" => $user,
                        "amount" => $amount
                    ];
                }, array_keys($expense->getParticipants()), $expense->getParticipants())
            ]));
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo(json_encode(["errors" => $e->getErrors()]));
        }
    }

    // Método para obtener los detalles de un gasto
    public function getExpense($groupId, $expenseId) {
        //$currentUser = $this->authenticateUser();
        try{
            $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
            if (!$expense) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                echo(json_encode(["error" => "Expense not found"]));
                return;
            }

            if ($expense->getGroup()->getId() != $groupId) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                echo(json_encode(["error" => "Expense does not belong to this group"]));
                return;
            }

            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header('Content-Type: application/json');
            echo(json_encode([
                "id" => $expense->getId(),
                "description" => $expense->getDescription(),
                "totalAmount" => $expense->getTotalAmount(),
                "payer" => $expense->getPayer()->getUsername(),
                "group" => $expense->getGroup()->getId(),
                "participants" => array_map(function ($username, $amount) {
                return array(
                    "username" => $username,
                    "amount" => $amount
                );
            }, array_keys($expense->getParticipants()), $expense->getParticipants())
            ]));
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo(json_encode(["errors" => $e->getErrors()]));
        }
    }

    // Método para actualizar un gasto
    public function updateExpense($groupId, $expenseId, $data) {
        $currentUser = $this->authenticateUser();

        $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
        if (!$expense) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            echo(json_encode(["error" => "Expense not found"]));
            return;
        }

        if ($expense->getGroup()->getId() != $groupId) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            echo(json_encode(["error" => "Expense does not belong to this group"]));
            return;
        }

        if (!($expense->getPayer() == $currentUser || $expense->getGroup()->getAdmin() == $currentUser)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            echo(json_encode(["error" => "You are not authorized to edit this expense"]));
            return;
        }
        if (isset($data->description)) {
            $expense->setDescription($data->description);
        }
        if (isset($data->totalAmount)) {
            $expense->setTotalAmount($data->totalAmount);
        }

        if (isset($data->participants)) {
            $expense->clearParticipants();
            foreach ($data->participants as $username => $amount) {
                $user = $this->userMapper->getUser($username);
                if ($user && floatval($amount) > 0) {
                    $expense->addParticipant($user, round(floatval($amount), 2));
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    echo(json_encode(["error" => "Invalid participant or amount: $username"]));
                    return;
                }
            }
        }
     
        try {
            $expense->checkIsValidForUpdate();
            $this->expenseMapper->update($expense);

            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header('Content-Type: application/json');
            echo(json_encode([
                "id" => $expense->getId(),
                "description" => $expense->getDescription(),
                "totalAmount" => $expense->getTotalAmount(),
                "payer" => $expense->getPayer(),
                "group" =>  $expense->getGroup()->getId(),
                "participants" => array_map(function ($user, $amount) {
                    return [
                        "username" => $user,
                        "amount" => $amount
                    ];
                }, array_keys($expense->getParticipants()), $expense->getParticipants())
            ]));
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo(json_encode(["errors" => $e->getErrors()]));
        }
    }

    // Método para eliminar un gasto
    public function deleteExpense($groupId, $expenseId) {
        $currentUser = $this->authenticateUser();

        $expense = $this->expenseMapper->getExpenseDetailsById($expenseId);
        if (!$expense) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            echo(json_encode(["error" => "Expense not found"]));
            return;
        }

        if ($expense->getGroup()->getId() != $groupId) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            echo(json_encode(["error" => "Expense does not belong to this group"]));
            return;
        }

        if (!($expense->getPayer()->getUsername() == $currentUser || $expense->getGroup()->getAdmin() == $currentUser)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            echo(json_encode(["error" => "You are not authorized to delete this expense"]));
            return;
        }

        $this->expenseMapper->delete($expense);

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        echo(json_encode(["message" => "Expense deleted successfully"]));
    }
}

// URI-MAPPING for this Rest endpoint
$expenseRest = new ExpenseRest();
URIDispatcher::getInstance()       
    ->map("GET", "/group/$1/expense/$2", array($expenseRest, "getExpense"))        
    ->map("POST", "/group/$1/expense", array($expenseRest, "addExpense"))          
    ->map("PUT", "/group/$1/expense/$2", array($expenseRest, "updateExpense"))     
    ->map("DELETE", "/group/$1/expense/$2", array($expenseRest, "deleteExpense")); 
