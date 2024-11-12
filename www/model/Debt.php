<?php
// file: model/Debt.php

require_once(__DIR__ . "/../config/ValidationException.php");

/**
 * Class Debt
 *
 * Represents a debt relationship between two users in a specific community.
 */
class Debt {

    /**
     * The id of this debt
     * @var int
     */
    private $id;

    /**
     * The debtor's username
     * @var string
     */
    private $debtor;

    /**
     * The creditor's username
     * @var string
     */
    private $creditor;

    /**
     * The id of the community associated with the debt
     * @var int
     */
    private $communityId;

    /**
     * The amount owed
     * @var float
     */
    private $amount;

    /**
     * The status of the debt ('pending' or 'paid')
     * @var string
     */
    private $status;

    /**
     * Debt constructor
     *
     * @param int $id
     * @param string $debtor
     * @param string $creditor
     * @param int $communityId
     * @param float $amount
     * @param string $status
     */
    public function __construct($id = null, $debtor = null, $creditor = null, $communityId = null, $amount = 0.0, $status = 'pending') {
        $this->id = $id;
        $this->debtor = $debtor;
        $this->creditor = $creditor;
        $this->communityId = $communityId;
        $this->amount = $amount;
        $this->status = $status;
    }

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getDebtor() {
        return $this->debtor;
    }

    public function setDebtor($debtor) {
        $this->debtor = $debtor;
    }

    public function getCreditor() {
        return $this->creditor;
    }

    public function setCreditor($creditor) {
        $this->creditor = $creditor;
    }

    public function getCommunityId() {
        return $this->communityId;
    }

    public function setCommunityId($communityId) {
        $this->communityId = $communityId;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Checks if the current instance is valid for creating or updating in the database.
     *
     * @throws ValidationException if the instance is not valid
     */
    public function checkIsValidForSave() {
        $errors = array();

        if (!isset($this->debtor) || trim($this->debtor) === '') {
            $errors["debtor"] = "Debtor username is mandatory";
        }
        if (!isset($this->creditor) || trim($this->creditor) === '') {
            $errors["creditor"] = "Creditor username is mandatory";
        }
        if ($this->debtor === $this->creditor) {
            $errors["debtor_creditor"] = "Debtor and creditor cannot be the same user";
        }
        if (!isset($this->communityId)) {
            $errors["communityId"] = "Community ID is mandatory";
        }
        if ($this->amount <= 0) {
            $errors["amount"] = "Amount must be a positive value";
        }
        if ($this->status !== 'pending' && $this->status !== 'paid') {
            $errors["status"] = "Status must be 'pending' or 'paid'";
        }

        if (sizeof($errors) > 0) {
            throw new ValidationException($errors, "Debt is not valid");
        }
    }
}
