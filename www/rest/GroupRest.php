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

		// json_encode Group objects.
		// since Group objects have private fields, the PHP json_encode will not
		// encode them, so we will create an intermediate array using getters and
		// encode it finally
		$groups_array = array();
		foreach($groups as $group) {
			array_push($groups_array, array(
				"id" => $group->getId(),
				"name" => $group->getName(),
				"description" => $group->getDescription(),
				"admin" => $group->getAdmin()->getUsername()
			));
		}

		header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
		header('Content-Type: application/json');
		echo(json_encode($groups_array));
	}

    public function readGroup($groupId) {
		// find the Group object in the database
		$group = $this->groupMapper->getGroupDetailsById($groupId);
		if ($group == NULL) {
			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
			echo("Group with id ".$groupId." not found");
			return;
		}

		$group_array = array(
            "id" => $group->getId(),
            "name" => $group->getName(),
            "description" => $group->getDescription(),
            "admin" => $group->getAdmin()->getUsername()
		);

		// Add members
		$group_array["members"] = array();
		foreach ($group->getMembers() as $username => $balance) {
			array_push($group_array["members"], array(
				"username" => $username,
				"balance" => $balance
			));
		}
		

		//add expenses
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
		echo(json_encode($group_array));
	}

	public function createGroup($data) {
		echo(json_encode($data));

		$currentUser = parent::authenticateUser();
		$group = new Group();

		if (isset($data->name) && isset($data->description)) {
			$group->setName($data->name);
			$group->setDescription($data->description);

			$group->setAdmin($currentUser);
		}
        
        if (isset($data->members)) {
            foreach ($data->members as $memberData) {
                $user = $this->userMapper->getUser($memberData);
				if ($user) {
					$group->addMember($user, 0);
				} else {
                    // Si un miembro no existe, retornar un error
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    header('Content-Type: application/json');
                    echo(json_encode(["error" => "Member not found: " . $memberData]));
                    return;
				}
            } 
        }

		try {
			// validate Group object
			$group->checkIsValidForCreate(); // if it fails, ValidationException

			// save the Group object into the database
			$groupId = $this->groupMapper->save($group);

			// response OK. Also send group in content
			header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
			header('Location: '.$_SERVER['REQUEST_URI']."/".$groupId);
			header('Content-Type: application/json');
			echo(json_encode(array(
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
			)));

		} catch (ValidationException $e) {
			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
			header('Content-Type: application/json');
			echo(json_encode($e->getErrors()));
		}
	}


	public function updateGroup($groupId, $data) {
		$currentUser = parent::authenticateUser();

		$group = $this->groupMapper->getGroupDetailsById($groupId);
		if ($group == NULL) {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not found');
			echo("Group with id ".$groupId." not found");
			return;
		}

		// Check if the Group author is the currentUser (in Session)
		if ($group->getAdmin() != $currentUser) {
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			echo("you are not the author of this group");
			return;
		}

		if (isset($data->name) && isset($data->description)) {
			$group->setName($data->name);
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
                    	}
                	 	else {
							$newMembers[$user->getUsername()] = 0;
						}  
           			} else {
						// Si un miembro no existe, retornar un error
						header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
						header('Content-Type: application/json');
						echo(json_encode(["error" => "Member not found: " . $memberData]));
						return;
					}
				}
			}

			echo(json_encode($newMembers));

            $group->clearMembers();	
            foreach ($newMembers as $username => $balance) {
                $user = $this->userMapper->getUser($username);
                if ($user) {
                    $group->addMember($user, $balance);
                }
            }
        }

		try {
			// validate Group object
			$group->checkIsValidForUpdate(); // if it fails, ValidationException
			$this->groupMapper->update($group);

			header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header('Content-Type: application/json');
			echo(json_encode(array(
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
			)));
		}catch (ValidationException $e) {
			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
			header('Content-Type: application/json');
			echo(json_encode($e->getErrors()));
		}
	}

	public function deleteGroup($groupId) {
		$currentUser = parent::authenticateUser();
		$group = $this->groupMapper->findById($groupId);

		if ($group == NULL) {
			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
			echo("Group with id ".$groupId." not found");
			return;
		}
		// Check if the Group author is the currentUser (in Session)
		if ($group->getAdmin() != $currentUser) {
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			echo("you are not the author of this group");
			return;
		}

		$this->groupMapper->delete($group);

		header($_SERVER['SERVER_PROTOCOL'].' 204 No Content');
	}
}

// URI-MAPPING for this Rest endpoint
$groupRest = new GroupRest();
URIDispatcher::getInstance()
	->map("GET", "/group", array($groupRest,"getGroups"))
	->map("GET", "/group/$1", array($groupRest,"readGroup"))
	->map("POST", "/group", array($groupRest,"createGroup"))
	->map("PUT", "/group/$1", array($groupRest,"updateGroup"))
	->map("DELETE", "/group/$1", array($groupRest,"deleteGroup"));
