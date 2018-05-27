<?php
namespace Bank\Service;

use DomainException;
use Bank\Model\Account;
use Bank\Service\AccountServiceInterface;
use Bank\Repository\AccountRepositoryInterface;

class AccountService implements AccountServiceInterface
{
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function getAccount($id)
    {
        return $this->accountRepository->getAccount($id);
    }

    public function openAccount($owner, $displayName)
    {
        $account = new Account($owner, $displayName);
        $this->validateAccount($account);
        $account = $this->accountRepository->saveAccount($account);
        return $account;
    }

    public function closeAccount($id)
    {
        $account = $this->loadAccount($id);
        $this->accountRepository->deleteAccount($id);
    }

    public function getAccountBalance($id)
    {
        $account = $this->loadAccount($id);
        return $account->getBalance();
    }

    private function validateAccount($account)
    {
        if ($account->getOwner() == null || $account->getOwner() == ""){
            throw new DomainException("owner of account cannot be null or empty");
        }
        if ($account->getDisplayName() == null || $account->getDisplayName() == ""){
            throw new DomainException("displayName of account cannot be null or empty");
        }
    }

    private function loadAccount($accountId)
    {
        $account = $this->accountRepository->getAccount($accountId);
        if ($account == null){
            throw new DomainException("account not found for id $accountId");
        }
        return $account;
    }

}