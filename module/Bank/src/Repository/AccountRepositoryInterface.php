<?php
namespace Bank\Repository;

use Bank\Model\Account;

interface AccountRepositoryInterface
{
    public function getAccount($id);

    public function saveAccount(Account $account);

    public function deleteAccount($id);
}