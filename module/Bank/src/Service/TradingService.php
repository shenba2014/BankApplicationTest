<?php
namespace Bank\Service;

use DomainException;
use Bank\Model\Transaction;
use Bank\Model\Account;
use Bank\Model\TransactionType;
use Bank\Service\TradingServiceInterface;
use Bank\Repository\AccountRepositoryInterface;
use Bank\Repository\TransactionRepositoryInterface;

/*
 * todo support db transaction
 */
class TradingService implements TradingServiceInterface
{
    private $accountRepository;
    private $transactionRepository;
    private $transferApprovalService;
    private $serviceCharge = 100.0;
    private $maxTransferAmountOfDay = 10000.0;

    public function __construct(AccountRepositoryInterface $accountRepository,
    TransactionRepositoryInterface $transactionRepository,
    TransferApprovalServiceInterface $transferApprovalService)
    {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transferApprovalService = $transferApprovalService;
    }

    public function withdraw($accountId, $amount)
    {
        $this->validateAmount($amount);
        $account = $this->loadAccount($accountId);
        $balance = $account -> deduct($amount);

        $this->saveAccount($account);
        $this->saveTransaction(new Transaction($account->getId(), -$amount
        , TransactionType::$Withdraw,"withdraw $amount", $balance));
    }

    public function deposit($accountId, $amount)
    {
        $this -> validateAmount($amount);
        $account = $this-> loadAccount($accountId);
        $balance = $account -> add($amount); 
        
        $this -> saveAccount($account);
        $this -> saveTransaction(new Transaction($account->getId(), $amount
        , TransactionType::$Deposit,"deposit $amount", $balance));
    }

    public function transfer($fromAccountId, $toAccountId, $amount)
    {
        $this -> validateAmount($amount);
        $fromAccount = $this->loadAccount($fromAccountId);
        $toAccount = $this->loadAccount($toAccountId);

        $this->checkBalance($fromAccount, $toAccount, $amount);
        $this->checkTotalTransferAmountOfDay($fromAccount, $amount);
        $this->approveTransfer($fromAccount, $toAccount);
        $this->executeTransfer($fromAccount, $toAccount, $amount);
        $this->deductServiceCharge($fromAccount, $toAccount);
    }

    private function validateAmount($amount)
    {
        if ($amount <= 0){
            throw new DomainException('invalid amount');
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

    private function saveAccount($account)
    {
        $this->accountRepository->saveAccount($account);
    }

    private function saveTransaction($transaction)
    {
        $this->transactionRepository->saveTransaction($transaction);
    }

    private function checkBalance($fromAccount, $toAccount, $transferAmount)
    {
        $deductAmount = $transferAmount;
        $transferBetweenDifferentOwner = !$fromAccount->isAccountFromSameOwner($toAccount);
        if ($transferBetweenDifferentOwner)
        {
            $deductAmount += $this->serviceCharge;
        }
        if ($fromAccount->getBalance() < $deductAmount)
        {
            $accountDisplayName = $fromAccount->getDisplayName();
            throw new DomainException("balance not enough in account $accountDisplayName");
        }
    }

    private function checkTotalTransferAmountOfDay($fromAccount, $transferAmount)
    {
        if ($transferAmount > $this->maxTransferAmountOfDay)
        {
            throw new DomainException("total transfer amount of a day cannot be more than $this->maxTransferAmountOfDay");
        }
        $today = date('Y-m-d');
        $transactions = $this->transactionRepository->getTransactionsByAccount($fromAccount->getId(), $today);
        $totalTransferAmountOfDay = 0;
        foreach ($transactions as $k => $v) {
            if ($v->getType() == TransactionType::$TransferOut){
                if ($totalTransferAmountOfDay < $this->maxTransferAmountOfDay)
                {
                    $totalTransferAmountOfDay += abs($v->getAmount());
                }
            }
        }
        if ($totalTransferAmountOfDay + $transferAmount > $this->maxTransferAmountOfDay)
        {
            throw new DomainException("total transfer amount of a day cannot be more than $this->maxTransferAmountOfDay");
        }
    }

    private function approveTransfer($fromAccount, $toAccount)
    {
        $transferBetweenDifferentOwner = !$fromAccount->isAccountFromSameOwner($toAccount);
        if ($transferBetweenDifferentOwner){
            $result = $this->transferApprovalService->approve();
            if (!$result){
                throw new DomainException("transfer approval is failed");
            }
        }
    }

    private function executeTransfer($fromAccount, $toAccount, $amount)
    {
        $fromAccountBalance = $fromAccount->deduct($amount);
        $toAccountBalance = $toAccount->add($amount);

        $this->saveAccount($fromAccount);
        $this->saveAccount($toAccount);

        $toAccountDisplayName = $toAccount->getDisplayName();
        $this->saveTransaction(new Transaction($fromAccount->getId(), -$amount,
        TransactionType::$TransferOut,"transfer $amount to account $toAccountDisplayName", $fromAccountBalance, $toAccount->getId(), null));

        $fromAccountDisplayName = $fromAccount->getDisplayName();
        $this->saveTransaction(new Transaction($toAccount->getId(), $amount,
        TransactionType::$TransferIn,"get $amount from account $fromAccountDisplayName", $toAccountBalance, null, $fromAccount->getId()));
    }

    private function deductServiceCharge($fromAccount, $toAccount){
        $transferBetweenDifferentOwner = !$fromAccount->isAccountFromSameOwner($toAccount);
        if ($transferBetweenDifferentOwner)
        {
            $fromAccountBalance = $fromAccount->deduct($this->serviceCharge);
            $toAccountDisplayName = $toAccount->getDisplayName();
            $this->saveAccount($fromAccount);
            $this->saveTransaction(new Transaction($fromAccount->getId(), -$this->serviceCharge,
            TransactionType::$ServiceCharge,"service charge $this->serviceCharge for transfering to account $toAccountDisplayName", $fromAccountBalance));
        }
    }
}