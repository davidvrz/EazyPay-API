<?php
// file: model/Expense.php

require_once(__DIR__ . "/../config/ValidationException.php");
require_once(__DIR__ . "/User.php");
require_once(__DIR__ . "/Group.php");

/**
 * Class Expense
 *
 * Represents an Expense within a group group. Each Expense
 * is associated with a specific group and has a payer.
 */
class Expense {
    
    /**
     * The ID of this expense
     * @var int
     */
    private $id;

    /**
     * The group to which this expense belongs
     * @var Group
     */
    private $group;

    /**
     * The description of this expense
     * @var string
     */
    private $description;

    /**
     * The total amount of this expense
     * @var float
     */
    private $totalAmount;

    /**
     * The date of this expense
     * @var DateTime
     */
    private $date;

    /**
     * The user who paid this expense
     * @var User
     */
    private $payer;

    private $participants;    


    /**
     * The constructor
     *
     * @param int $id The ID of the expense
     * @param Group $group The group to which this expense belongs
     * @param string $description The description of the expense
     * @param float $totalAmount The total amount of the expense
     * @param User $payer The user who paid the expense
     */
    public function __construct($id = null, Group $group= null, $description = null, $totalAmount = null, User $payer = null, array $participants = []) {
        $this->id = $id;
        $this->group = $group;
        $this->description = $description;
        $this->totalAmount = $totalAmount;
        $this->date = new DateTime(); // Sets to current date/time
        $this->payer = $payer;
        $this->participants = $participants;
    }

    /**
     * Gets the ID of this expense
     *
     * @return int The ID of this expense
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets the group associated with this expense
     *
     * @return Group The group associated with this expense
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * Sets the group associated with this expense
     *
     * @param Group $group The group associated with this expense
     * @return void
     */
    public function setGroup( Group $group) {
        $this->group = $group;
    }

    /**
     * Gets the description of this expense
     *
     * @return string The description of this expense
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Sets the description of this expense
     *
     * @param string $description The description of this expense
     * @return void
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Gets the total amount of this expense
     *
     * @return float The total amount of this expense
     */
    public function getTotalAmount() {
        return $this->totalAmount;
    }

    /**
     * Sets the total amount of this expense
     *
     * @param float $totalAmount The total amount of this expense
     * @return void
     */
    public function setTotalAmount($totalAmount) {
        $this->totalAmount = $totalAmount;
    }

    /**
     * Gets the date of this expense
     *
     * @return DateTime The date of this expense
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Gets the payer of this expense
     *
     * @return User The payer of this expense
     */
    public function getPayer() {
        return $this->payer;
    }

    /**
     * Sets the payer of this expense
     *
     * @param User $payer The payer of this expense
     * @return void
     */
    public function setPayer(User $payer) {
        $this->payer = $payer;
    }

    public function getParticipants() {
        return $this->participants;
    }

    public function setParticipants(array $participants) {
		$this->participants = $participants;
	}
    
    public function addParticipant(User $user, $amount) {
        $this->participants[$user->getUsername()] = $amount;
    }
    
    public function clearParticipants() {
		$this->participants = []; // Clears the member list
	}

    /**
     * Checks if the current instance is valid for being created in the database.
     *
     * @throws ValidationException if the instance is not valid
     *
     * @return void
     */
    public function checkIsValidForCreate() {
        $errors = array();
    
        // Validación de descripción
        if (strlen(trim($this->description)) == 0) {
            $errors["description"] = "Expense description is mandatory.";
        }
    
        // Validación de monto total
        if ($this->totalAmount <= 0) {
            $errors["totalAmount"] = "Total amount must be greater than zero.";
        }
    
        // Validación de grupo
        if ($this->group == null) {
            $errors["group"] = "Group is mandatory.";
        }
    
        // Validación de pagador
        if (empty($this->payer)) {
            $errors["payer"] = "Payer is mandatory.";
        }
    
        // Validación de participantes
        if (empty($this->participants)) {
            $errors["participants"] = "At least one participant is required.";
        } else {
            $participantsTotalAmount = 0; // Variable para sumar los montos de los participantes

            foreach ($this->participants as $username => $amount) {
                // Comprobar que el monto del participante sea válido
                if ($amount <= 0) {
                    $errors["participant_amount"] = "Each participant must have a valid amount.";
                }

                $participantsTotalAmount += $amount;
            }
            
            // Validación de la suma de los montos de los participantes, permitiendo una diferencia mínima de 0.01
            if (round(abs($participantsTotalAmount - $this->totalAmount),2) > 0.01) {
                $errors["participants_total"] = "The sum of participants' amounts must equal the total amount.";
            }
        }

        // Lanza excepción si hay errores
        if (sizeof($errors) > 0) {
            throw new ValidationException($errors, "Expense is not valid");
        }

    }
    

    public function checkIsValidForUpdate() {
		$errors = array();
	
		if (!isset($this->id)) {
			$errors["id"] = "id is mandatory";
		}
	
		try {
			$this->checkIsValidForCreate();
		} catch (ValidationException $ex) {
			foreach ($ex->getErrors() as $key => $error) {
				$errors[$key] = $error;
			}
		}
	
		if (sizeof($errors) > 0) {
			throw new ValidationException($errors, "expense is not valid");
		}
	}
}
