<?php
namespace Bank\Model;

class Transaction
{
    private $id;
    private $accountId;
    private $amount;
    private $type;
    private $transferToAccountId;
    private $transferFromAccountId;
    private $description;
    private $createdDate;
    private $balance;

    public function __construct($accountId = null
    , $amount = null, $type = null, $description = null, $balance = null
    , $transferToAccountId = null
    , $transferFromAccountId = null){
        $this->accountId = $accountId;
        $this->amount = $amount;
        $this->type = $type;
        $this->description = $description;
        $this->balance = $balance;
        $this->createdDate = date('Y-m-d G:i:s', time());
        $this->transferToAccountId = $transferToAccountId;
        $this->transferFromAccountId = $transferFromAccountId;
    }

    public function exchangeArray(array $data)
    {
        $this->id     = !empty($data['id']) ? $data['id'] : null;
        $this->accountId = !empty($data['accountId']) ? $data['accountId'] : null;
        $this->amount  = !empty($data['amount']) ? $data['amount'] : 0;
        $this->type  = !empty($data['type']) ? $data['type'] : 0;
        $this->description  = !empty($data['description']) ? $data['description'] : 0;
        $this->balance  = !empty($data['balance']) ? $data['balance'] : 0;
        $this->createdDate  = !empty($data['createdDate']) ? $data['createdDate'] : null;
        $this->transferToAccountId  = !empty($data['transferToAccountId']) ? $data['transferToAccountId'] : null;
        $this->transferFromAccountId  = !empty($data['transferFromAccountId']) ? $data['transferFromAccountId'] : false;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getAccountId(){
        return $this->accountId;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function getType(){
        return $this->type;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getCreatedDate(){
        return $this->createdDate;
    }

    public function getTransferToAccountId(){
        return $this->transferToAccountId;
    }

    public function getTransferFromAccountId(){
        return $this->transferFromAccountId;
    }

    public function getBalance(){
        return $this->balance;
    }
}