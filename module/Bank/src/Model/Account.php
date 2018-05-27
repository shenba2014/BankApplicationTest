<?php
namespace Bank\Model;

use DomainException;

class Account
{
    private $id;
    private $owner;
    private $displayName;
    private $balance;
    private $createdDate;
    private $updatedDate;
    private $isDeleted;

    public function __construct($owner = null, $displayName = null){
        $this->id = 0;
        $this->owner = $owner;
        $this->displayName = $displayName;
        $this->balance = 0.0;
        $this->createdDate = date('Y-m-d G:i:s');
        $this->updatedDate = date('Y-m-d G:i:s');
        $this->isDeleted = false;
    }

    public function exchangeArray(array $data)
    {
        $this->id     = !empty($data['id']) ? $data['id'] : null;
        $this->owner = !empty($data['owner']) ? $data['owner'] : null;
        $this->displayName  = !empty($data['displayName']) ? $data['displayName'] : null;
        $this->balance  = !empty($data['balance']) ? $data['balance'] : 0;
        $this->createdDate  = !empty($data['createdDate']) ? $data['createdDate'] : null;
        $this->updatedDate  = !empty($data['updatedDate']) ? $data['updatedDate'] : null;
        $this->isDeleted  = !empty($data['isDeleted']) ? $data['isDeleted'] : false;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getOwner(){
        return $this->owner;
    }

    public function getDisplayName(){
        return $this->displayName;
    }

    public function getBalance(){
        return $this->balance;
    }

    public function deduct($amount){
        if ($this->balance < $amount)
        {
            throw new DomainException("balance not enough in account $this->displayName");
        }
        $this->balance -= $amount;
        return $this->balance;
    }

    public function add($amount){
        $this->balance += $amount;
        return $this->balance;
    }

    public function getCreatedDate(){
        return $this->createdDate;
    }

    public function setCreatedDate($createdDate){
        $this->createdDate = $createdDate;
    }

    public function getUpdatedDate(){
        return $this->updatedDate;
    }

    public function setUpdatedDate($updatedDate){
        $this->updatedDate = $updatedDate;
    }

    public function getIsDeleted(){
        return $this->isDeleted;
    }

    public function setIsDeleted($isDeleted){
        $this->isDeleted = $isDeleted;
    }

    public function isAccountFromSameOwner(Account $account)
    {
        return $this->owner == $account->getOwner();
    }
}