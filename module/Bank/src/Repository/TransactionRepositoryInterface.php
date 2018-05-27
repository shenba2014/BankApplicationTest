<?php
namespace Bank\Repository;

use Bank\Model\Transaction;

interface TransactionRepositoryInterface
{
    public function saveTransaction(Transaction $transaction);

    public function getTransactionsByAccount($accountId, $date);
}