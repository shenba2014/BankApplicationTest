<?php
namespace Bank\Repository;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGatewayInterface;
use Bank\Model\Transaction;

class DbTransactionRepository implements TransactionRepositoryInterface
{
    private $transactionTableGateway;

    public function __construct(TableGatewayInterface $transactionTableGateway)
    {
        $this->transactionTableGateway = $transactionTableGateway;
    }

    public function saveTransaction(Transaction $transaction)
    {
        $data = [
                    'accountId' => $transaction->getAccountId(),
                    'amount'  => $transaction->getAmount(),
                    'type' => $transaction->getType(),
                    'balance' => $transaction->getBalance(),
                    'description' => $transaction->getDescription(),
                    'createdDate' => $transaction->getCreatedDate(),
                    'transferToAccountId' => $transaction->getTransferToAccountId(),
                    'transferFromAccountId' => $transaction->getTransferFromAccountId()
                ];

        $this->transactionTableGateway->insert($data);
        $transaction->setId($this->transactionTableGateway->lastInsertValue);
        return $transaction;
    }

    public function getTransactionsByAccount($accountId, $date)
    {
        $results = [];
        
        $rowset = $this->transactionTableGateway->select(function (Select $select) use($date, $accountId) {
            $endDate = date('Y-m-d', strtotime($date. ' + 1 days'));
            $select->where->between('createdDate', $date, $endDate);
            $select->where->equalTo('accountId', $accountId);
        });
        foreach ($rowset as $row) {
            $results[] = $row;
        }
        return $results;
    }
}