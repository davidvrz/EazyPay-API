<?php
// file: model/Expense.php

require_once(__DIR__ . "/../config/ValidationException.php");
require_once(__DIR__ . "/User.php");
require_once(__DIR__ . "/Group.php");

/**
 * Class Expense
 *
 * Represents an Expense within a community group. Each Expense
 * is associated with a specific community and has a payer.
 */
class Expense {
    
    /**
     * The ID of this expense
     * @var int
     */
    private $id;

    /**
     * The community to which this expense belongs
     * @var Group
     */
    private $community;

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

    /**
     * The constructor
     *
     * @param int $id The ID of the expense
     * @param Group $community The community to which this expense belongs
     * @param string $description The description of the expense
     * @param float $totalAmount The total amount of the expense
     * @param User $payer The user who paid the expense
     */
    public function __construct($id = null, Group $community = null, $description = null, $totalAmount = null, User $payer = null) {
        $this->id = $id;
        $this->community = $community;
        $this->description = $description;
        $this->totalAmount = $totalAmount;
        $this->date = new DateTime(); // Sets to current date/time
        $this->payer = $payer;
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
     * Gets the community associated with this expense
     *
     * @return Group The community associated with this expense
     */
    public function getCommunity() {
        return $this->community;
    }

    /**
     * Sets the community associated with this expense
     *
     * @param Group $community The community associated with this expense
     * @return void
     */
    public function setCommunity(Group $community) {
        $this->community = $community;
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

    /**
     * Checks if the current instance is valid for being created in the database.
     *
     * @throws ValidationException if the instance is not valid
     *
     * @return void
     */
    public function checkIsValidForCreate() {
        $errors = array();
        if (empty(trim($this->description))) {
            $errors["description"] = "Expense description is mandatory.";
        }
        if ($this->totalAmount <= 0) {
            $errors["totalAmount"] = "Total amount must be greater than zero.";
        }
        if ($this->community == null) {
            $errors["community"] = "Community is mandatory.";
        }
        if ($this->payer == null) {
            $errors["payer"] = "Payer is mandatory.";
        }

        if (sizeof($errors) > 0) {
            throw new ValidationException($errors, "Expense is not valid");
        }
    }
}
