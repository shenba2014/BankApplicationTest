<?php
namespace Bank\Service;

interface AccountServiceInterface
{
    public function getAccount($id);

    public function openAccount($owner, $displayName);

    public function closeAccount($id);

    public function getAccountBalance($id);
}