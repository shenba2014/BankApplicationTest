<?php
namespace Bank\Service;

interface TradingServiceInterface
{
    public function withdraw($accountId, $amount);

    public function deposit($accountId, $amount);

    public function transfer($fromAccountId, $toAccountId, $amount);
}