<?php

namespace Bank\Test\Service;

use Bank\Model\Account;
use Bank\Service\AccountService;
use Bank\Repository\FakeAccountRepository;
use PHPUnit_Framework_TestCase as TestCase;

class AccountServiceTest extends TestCase
{
    private $accountRepository;
    private $accountService;

    protected function setup()
    {
        $this->accountRepository = new FakeAccountRepository();
        $this->accountService = new AccountService($this->accountRepository);
    }

    public function testShouldOpenAccountSuccess()
    {
        $owner = "user1";
        $displayName = "home";
        $account = $this->accountService->openAccount($owner, $displayName);
       
        $savedAccount = $this->accountRepository->getAccount($account->getId());
        
        $this->assertEquals($account->getId(), $savedAccount->getId());
        $this->assertEquals($owner, $savedAccount->getOwner());
        $this->assertEquals($displayName, $savedAccount->getDisplayName());
        $this->assertEquals(0.0, $savedAccount->getBalance());
        $this->assertEquals(0, $savedAccount->getIsDeleted());
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldOpenAccoutnFailWhenOwnerNameIsEmpty()
    {
        $owner = "";
        $displayName = "home";
        $account = $this->accountService->openAccount($owner, $displayName);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldOpenAccoutnFailWhenDisplaynameIsEmpty()
    {
        $owner = "user1";
        $displayName = "";
        $account = $this->accountService->openAccount($owner, $displayName);
    }

    public function testShouldGetAccountBalanceSuccess()
    {
        $owner = "user1";
        $displayName = "home";
        $account = new Account($owner, $displayName);
        $account->add(10.0);
        $this->accountRepository->saveAccount($account);

        $balance = $this->accountService->getAccountBalance($account->getId());
        
        $this->assertEquals(10.0, $balance);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldGetAccountBalanceFailWhenAccountNotFound()
    {
        $balance = $this->accountService->getAccountBalance("invalid id");
    }

    public function testShouldCloseAccountSuccess()
    {
        $owner = "user1";
        $displayName = "home";
        $account = new Account($owner, $displayName);
        $this->accountRepository->saveAccount($account);

        $this->accountService->closeAccount($account->getId());

        $savedAccount = $this->accountRepository->getAccount($account->getId());
        $this->assertNull($savedAccount);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldCloseAccountFailWhenAccountNotFound()
    {
        $balance = $this->accountService->closeAccount("invalid id");
    }
}