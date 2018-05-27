<?php
namespace Bank\Model;

class TransactionType{
    public static $Withdraw = 1;
    public static $Deposit = 2;
    public static $TransferIn = 3;
    public static $TransferOut = 4;
    public static $ServiceCharge = 5;
}