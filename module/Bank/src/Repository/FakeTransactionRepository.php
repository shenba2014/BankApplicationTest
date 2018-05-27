<?php
namespace Bank\Repository;

use DomainException;
use Bank\Model\Transaction;
use Bank\Repository\TransactionRepositoryInterface;

class FakeTransactionRepository implements TransactionRepositoryInterface
{
    private $transactions = [];

    public function saveTransaction(Transaction $transaction)
    {
        $id = uniqid();
        $transaction->setId($id);
        $this->transactions[$id] = $transaction;
        return $transaction;
    }

    public function getTransactionsByAccount($accountId, $date)
    {
        $results = [];
        foreach ($this->transactions as $k => $v) {
            if ($v->getAccountId() == $accountId){
                if ($date == null 
                || $date == date('Y-m-d', strtotime($v->getCreatedDate())))
                {
                    array_push($results, $v);
                }
            }
        }
        return $results;
    }
}