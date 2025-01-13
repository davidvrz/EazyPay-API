<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");

require_once(__DIR__."/../model/Group.php");
require_once(__DIR__."/../model/GroupMapper.php");

require_once(__DIR__."/../model/Expense.php");
require_once(__DIR__."/../model/ExpenseMapper.php");

require_once(__DIR__."/BaseRest.php");

/**
* Class GroupRest
*
* It contains operations for creating, retrieving, updating, deleting and
* listing groups, as well as to create expenses to groups.
*
* Methods gives responses following Restful standards. Methods of this class
* are intended to be mapped as callbacks using the URIDispatcher class.
*
*/
class GroupRest extends BaseRest {
    private $groupMapper;
    private $userMapper;

    public function __construct() {
        parent::__construct();

        $this->groupMapper = new GroupMapper();
        $this->userMapper = new UserMapper();
    }

    public function getGroups() {
        $currentUser = parent::authenticateUser();
        $groups = $this->groupMapper->findAll($currentUser->getUsername());

        $groups_array = array();
        foreach ($groups as $group) {
            array_push($groups_array, array(
                "id" => $group->getId(),
                "name" => $group->getName(),
                "description" => $group->getDescription(),
                "admin" => $group->getAdmin()->getUsername()
            ));
        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
        header('Content-Type: application/json');
        echo json_encode(["data" => $groups_array]);
    }

    public function readGroup($groupId) {
        $currentUser = parent::authenticateUser();
        $group = $this->groupMapper->getGroupDetailsById($groupId);

        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
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

        $group_array = array(
            "id" => $group->getId(),
            "name" => $group->getName(),
            "description" => $group->getDescription(),
            "admin" => $group->getAdmin()->getUsername()
        );

        $group_array["members"] = array();
        foreach ($group->getMembers() as $username => $balance) {
            array_push($group_array["members"], array(
                "username" => $username,
                "balance" => $balance
            ));
        }

        $group_array["expenses"] = array();
        foreach ($group->getExpenses() as $expense) {
            array_push($group_array["expenses"], array(
                "id" => $expense->getId(),
                "description" => $expense->getDescription(),
                "payer" => $expense->getPayer()->getusername(),
                "total_amount" => $expense->getTotalAmount()
            ));
        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
        header('Content-Type: application/json');
        echo json_encode(["data" => $group_array]);
    }

    public function createGroup($data) {
        $currentUser = parent::authenticateUser();
        $group = new Group();
        
        if (isset($data->name)) {
            $group->setName($data->name);
        }

        if (isset($data->description)) {
            $group->setDescription($data->description);
        }

        $group->setAdmin($currentUser);
        
        if (isset($data->members)) {
            foreach ($data->members as $memberData) {
                $user = $this->userMapper->getUser($memberData);
                if ($user) {
                    $group->addMember($user, 0);
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    header('Content-Type: application/json');
                    echo json_encode(["errors" => "error-member-not-found"]);
                    return;
                }
            } 
        }

        try {
            $group->checkIsValidForCreate();
            $groupId = $this->groupMapper->save($group);

            header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
            header('Location: '.$_SERVER['REQUEST_URI']."/".$groupId);
            header('Content-Type: application/json');
            echo json_encode(["data" => array(
                "id" => $group->getId(),
                "name" => $group->getName(),
                "description" => $group->getDescription(),
                "admin" => $group->getAdmin()->getUsername(),
                "members" => array_map(function ($username, $balance) {
                    return array(
                        "username" => $username,
                        "balance" => $balance
                    );
                }, array_keys($group->getMembers()), $group->getMembers())
            )]);
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
            header('Content-Type: application/json');
            echo json_encode(["errors" => $e->getErrors()]);
        }
    }

    public function updateGroup($groupId, $data) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->getGroupDetailsById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);
            return;
        }

        if ($group->getAdmin() != $currentUser) {
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-not-a-group-member"]);
            return;
        }

        if (isset($data->name)) {
            $group->setName($data->name);
        }

        if (isset($data->description)) {
            $group->setDescription($data->description);
        }

        if (isset($data->members)) {
            $existingMembers = $group->getMembers();
            $newMembers = [];
            foreach ($data->members as $memberData) {
                if (isset($memberData)) {
                    $user = $this->userMapper->getUser($memberData);
                    if ($user) {
                        if (isset($existingMembers[$user->getUsername()])) {
                            $newMembers[$user->getUsername()] = $existingMembers[$user->getUsername()];
                        } else {
                            $newMembers[$user->getUsername()] = 0;
                        }
                    } else {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                        header('Content-Type: application/json');
                        echo json_encode(["errors" => "error-member-not-found"]);
                        return;
                    }
                }
            }

            $group->clearMembers();    
            foreach ($newMembers as $username => $balance) {
                $user = $this->userMapper->getUser($username);
                if ($user) {
                    $group->addMember($user, $balance);
                }
            }
        }

        try {
            $group->checkIsValidForUpdate();
            $this->groupMapper->update($group);

            header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
            header('Content-Type: application/json');
            echo json_encode(["data" => array(
                "id" => $group->getId(),
                "name" => $group->getName(),
                "description" => $group->getDescription(),
                "admin" => $group->getAdmin()->getUsername(),
                "members" => array_map(function ($username, $balance) {
                    return array(
                        "username" => $username,
                        "balance" => $balance
                    );
                }, array_keys($group->getMembers()), $group->getMembers())
            )]);
        } catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
            header('Content-Type: application/json');
            echo json_encode(["errors" => $e->getErrors()]);
        }
    }

    public function deleteGroup($groupId) {
        $currentUser = parent::authenticateUser();
        $group = $this->groupMapper->findById($groupId);

        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);
            return;
        }

        if ($group->getAdmin() != $currentUser) {
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-not-group-admin"]);
            return;
        }

        $this->groupMapper->delete($group);

        header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
        header('Content-Type: application/json');
        echo json_encode(["message" => "group-delete-successfully"]);
    }

    public function getMovements($groupId) {
        $currentUser = parent::authenticateUser();

        $group = $this->groupMapper->getGroupDetailsById($groupId);
        if (!$group) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-group-not-found"]);
            return;
        }

        if (!($this->groupMapper->isGroupMember($currentUser->getUsername(), $groupId))) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(["errors" => "error-not-a-group-member"]);
            return;
        }

        $members = $group->getMembers();
        $suggestedMovements = $this->calculateMovements($members);

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
        header('Content-Type: application/json');
        echo json_encode(["data" => $suggestedMovements]);
    }

    private function calculateMovements($members) {
        $movements = [];

        $debtors = [];
        $creditors = [];

        foreach ($members as $user => $balance) {
            if ($balance < 0) {
                $debtors[] = ["user" => $user, "amount" => abs($balance)];
            } elseif ($balance > 0) {
                $creditors[] = ["user" => $user, "amount" => $balance];
            }
        }

        foreach ($debtors as &$debtor) {
            foreach ($creditors as &$creditor) {
                if ($debtor["amount"] == 0) break;

                $amountToTransfer = min($debtor["amount"], $creditor["amount"]);

                $movements[] = [
                    "from" => $debtor["user"],
                    "to" => $creditor["user"],
                    "amount" => $amountToTransfer
                ];

                $debtor["amount"] -= $amountToTransfer;
                $creditor["amount"] -= $amountToTransfer;
            }
        }

        return $movements;
    }
}

// URI-MAPPING for this Rest endpoint
$groupRest = new GroupRest();
URIDispatcher::getInstance()
    ->map("GET", "/group", array($groupRest,"getGroups"))
    ->map("GET", "/group/$1", array($groupRest,"readGroup"))
    ->map("GET", "/group/$1/movements", array($groupRest, "getMovements"))
    ->map("POST", "/group", array($groupRest,"createGroup"))
    ->map("PUT", "/group/$1", array($groupRest,"updateGroup"))
    ->map("DELETE", "/group/$1", array($groupRest,"deleteGroup"));
