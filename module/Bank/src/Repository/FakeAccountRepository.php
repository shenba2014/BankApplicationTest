<?php
namespace Bank\Repository;

use RuntimeException;
use Bank\Model\Account;
use Bank\Repository\AccountRepositoryInterface;

class FakeAccountRepository implements AccountRepositoryInterface
{
    private $accounts = [];

    public function getAccount($id)
    {
        if (! isset($this->accounts[$id])) {
            return null;
        }

        $account = $this->accounts[$id];
        if ($account->getIsDeleted())
        {
            return null;
        }

        return $account;
    }

    public function saveAccount(Account $account)
    {
        if ($account->getId() == null){
            $id = uniqid();
            $account->setId($id);
            $this->accounts[$id] = $account;
            return $account;
        }
        $id = $account->getId();
        $this->accounts[$id] = $account;
        return $account;
    }

    public function deleteAccount($id)
    {
        if (! isset($this->accounts[$id])) {
            throw new RuntimeException(sprintf('Account by id "%s" not found', $id));
        }
        $this->accounts[$id]->setIsDeleted(1);
    }
}