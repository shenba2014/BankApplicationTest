<?php

namespace Bank\Test\Service;

use Bank\Model\Account;
use Bank\Model\Transaction;
use Bank\Model\TransactionType;
use Bank\Service\AccountService;
use Bank\Service\TradingService;
use Bank\Service\FakeTransferApprovalService;
use Bank\Repository\FakeAccountRepository;
use Bank\Repository\FakeTransactionRepository;
use PHPUnit_Framework_TestCase as TestCase;

class TradingServiceTest extends TestCase
{
    private $accountRepository;
    private $transactionRepository;
    private $transferApprovalService;
    private $tradingService;

    protected function setup()
    {
        $this->accountRepository = new FakeAccountRepository();
        $this->transactionRepository = new FakeTransactionRepository();
        $this->transferApprovalService = new FakeTransferApprovalService();
        $this->tradingService = new TradingService($this->accountRepository
        , $this->transactionRepository, $this->transferApprovalService);
    }

    public function testShouldWithdrawSuccess()
    {
        $owner = "user1";
        $displayName = "home";
        $account = new Account($owner, $displayName);
        $account->add(100);
        $this->accountRepository->saveAccount($account);

        $this->tradingService->withdraw($account->getId(), 10);
        $transactions = $this->transactionRepository->getTransactionsByAccount($account->getId(), date('Y-m-d'));

        $this->assertBalance($account, 90.0);
        $this->assertEquals(1, count($transactions));
        $this->assertTransaction($transactions[0], TransactionType::$Withdraw, -10, 90);
    }

     /**
     * @expectedException DomainException
     */
    public function testShouldWithdrawFailWhenAccountIsNotFound()
    {
        $this->tradingService->withdraw("invalid id", 10);
    }

     /**
     * @expectedException DomainException
     */
    public function testShouldWithdrawFailWhenBalanceIsNotEnough()
    {
        $owner = "user1";
        $displayName = "home";
        $account = new Account($owner, $displayName);
        $this->accountRepository->saveAccount($account);
        $account->add(100);

        $this->tradingService->withdraw($account->getId(), 101);
    }

    public function testShouldDepositSuccess()
    {
        $owner = "user1";
        $displayName = "home";
        $account = new Account($owner, $displayName);
        $this->accountRepository->saveAccount($account);
        $account->add(100);

        $this->tradingService->deposit($account->getId(), 10);
        $transactions = $this->transactionRepository->getTransactionsByAccount($account->getId(), date('Y-m-d'));

        $this->assertBalance($account, 110.0);
        $this->assertEquals(1, count($transactions));
        $this->assertTransaction($transactions[0], TransactionType::$Deposit, 10, 110);
    }

     /**
     * @expectedException DomainException
     */
    public function testShouldDepositFailWhenAccountNotFound()
    {
        $this->tradingService->deposit("invalid id", 10);
    }

    public function testShouldTransferWithServiceChargeBetweenDifferentOwner()
    {
        $owner1 = "user1";
        $displayName = "home";
        $fromAccount = new Account($owner1, $displayName);
        $this->accountRepository->saveAccount($fromAccount);
        $fromAccount->add(200);

        $owner2 = "user2";
        $displayName = "home";
        $toAccount = new Account($owner2, $displayName);
        $this->accountRepository->saveAccount($toAccount);
        $toAccount->add(100);

        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 10);
        $transactionsOfFromAccount = $this->transactionRepository->getTransactionsByAccount($fromAccount->getId(), date('Y-m-d'));
        $transactionsOfToAccount = $this->transactionRepository->getTransactionsByAccount($toAccount->getId(), date('Y-m-d'));
    
        $this->assertBalance($fromAccount, 90.0);
        $this->assertBalance($toAccount, 110.0);

        $transferOutTransactions = $this->filterTransactionsByType($transactionsOfFromAccount, TransactionType::$TransferOut);
        $this->assertEquals(1, count($transferOutTransactions));
        $this->assertTransaction($transferOutTransactions[0], TransactionType::$TransferOut, -10, 190, null, $toAccount->getId());

        $serviceChargeTransactions = $this->filterTransactionsByType($transactionsOfFromAccount, TransactionType::$ServiceCharge);
        $this->assertEquals(1, count($serviceChargeTransactions));
        $this->assertTransaction($serviceChargeTransactions[0], TransactionType::$ServiceCharge, -100, 90);

        $this->assertEquals(1, count($transactionsOfToAccount));
        $this->assertTransaction($transactionsOfToAccount[0], TransactionType::$TransferIn, 10, 110, $fromAccount->getId(), null);
    }

    public function testShouldTransferWithoutServiceChargeWithinSameOwner()
    {
        $owner = "user1";

        $displayName = "home";
        $fromAccount = new Account($owner, $displayName);
        $fromAccount->add(100);
        $this->accountRepository->saveAccount($fromAccount);
        
        $displayName = "office";
        $toAccount = new Account($owner, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
        
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 10);
        $transactionsInFromAccount = $this->transactionRepository->getTransactionsByAccount($fromAccount->getId(), date('Y-m-d'));
        $transactionsInToAccount = $this->transactionRepository->getTransactionsByAccount($toAccount->getId(), date('Y-m-d'));
    
        $this->assertBalance($fromAccount, 90.0);
        $this->assertBalance($toAccount, 110.0);

        $this->assertEquals(1, count($transactionsInFromAccount));
        $this->assertTransaction($transactionsInFromAccount[0], TransactionType::$TransferOut, -10, 90, null, $toAccount->getId());

        $this->assertEquals(1, count($transactionsInToAccount));
        $this->assertTransaction($transactionsInToAccount[0], TransactionType::$TransferIn, 10, 110, $fromAccount->getId(), null);
    }

     /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenFromAccountIsNotFound()
    {
        $owner = "user1";
        $displayName = "home";
        $fromAccount = new Account($owner, $displayName);
        $fromAccount->add(100);
        $this->accountRepository->saveAccount($fromAccount);
        
        $this->tradingService->transfer($fromAccount->getId(), "invalid id", 101);
    }

      /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenToAccountIsNotFound()
    {
        $owner = "user2";
        $displayName = "home";
        $toAccount = new Account($owner, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
        
        $this->tradingService->transfer("invalid id", $toAccount->getId(), 101);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenBalanceOfFromAccountIsNotEnough()
    {
        $owner = "user1";
        $displayName = "home";
        $fromAccount = new Account($owner, $displayName);
        $fromAccount->add(100);
        $this->accountRepository->saveAccount($fromAccount);
        
        $owner = "user2";
        $displayName = "home";
        $toAccount = new Account($owner, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
        
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 101);
    }


     /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenApprovalIsRejected()
    {
        $this->transferApprovalService->reject();

        $owner1 = "user1";
        $displayName = "home";
        $fromAccount = new Account($owner1, $displayName);
        $fromAccount->add(200);
        $this->accountRepository->saveAccount($fromAccount);
        
        $owner2 = "user2";
        $displayName = "home";
        $toAccount = new Account($owner2, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
       
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 10);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenTransferAmountIsOver10000()
    {
        $owner = "user1";

        $displayName = "home";
        $fromAccount = new Account($owner, $displayName);
        $fromAccount->add(20000);
        $this->accountRepository->saveAccount($fromAccount);
        
        $displayName = "office";
        $toAccount = new Account($owner, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
        
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 10001);
    }

    /**
     * @expectedException DomainException
     */
    public function testShouldTransferFailWhenTotalTransferAmountOfDayIsOver10000()
    {
        $owner = "user1";

        $displayName = "home";
        $fromAccount = new Account($owner, $displayName);
        $fromAccount->add(20000);
        $this->accountRepository->saveAccount($fromAccount);
        
        $displayName = "office";
        $toAccount = new Account($owner, $displayName);
        $toAccount->add(100);
        $this->accountRepository->saveAccount($toAccount);
        
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 10000);
        $this->tradingService->transfer($fromAccount->getId(), $toAccount->getId(), 1);
    }

    private function filterTransactionsByType($transactions, $type)
    {
        $results = [];
        foreach ($transactions as $k => $v) {
            if ($v->getType() == $type){
                array_push($results, $v);
            }
        }
        return $results;
    }

    private function assertBalance($account, $expectBalance)
    {
        $this->assertEquals($account->getBalance(), $expectBalance);
    }

    private function assertTransaction($transaction, $expectType
    , $expectAmount, $expectBalance
    , $transferFromAccountId = null
    , $transferToAccountId = null)
    {
        $this->assertEquals($transaction->getType(), $expectType);
        $this->assertEquals($transaction->getAmount(), $expectAmount);
        $this->assertEquals($transaction->getBalance(), $expectBalance);

        if ($transferFromAccountId != null){
            $this->assertEquals($transaction->getTransferFromAccountId(), $transferFromAccountId);
        }

        if ($transferToAccountId != null){
            $this->assertEquals($transaction->getTransferToAccountId(), $transferToAccountId);
        }
    }
}