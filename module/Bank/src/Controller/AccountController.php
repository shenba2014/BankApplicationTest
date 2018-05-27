<?php
namespace Bank\Controller;

use Zend\View\Model\JsonModel;
use Bank\Service\AccountServiceInterface;

class AccountController extends BaseController
{
    private $accountService;

    public function __construct(AccountServiceInterface  $accountService)
    {
        $this->accountService = $accountService;
    }

    public function getAction()
    {
        $id = $this->params()->fromQuery('id');
        $account = $this->accountService->getAccount($id);
        if (!$account)
        {
            return $this->jsonResult(null, true);
        }
        return $this->jsonResult(
            array(
                'accountId'=>$account->getId(), 
                'owner'=>$account->getOwner(),
                'displayName'=>$account->getDisplayName(),
                'balance'=>$account->getBalance(),
                'createdDate'=>$account->getCreatedDate(),
                'updatedDate'=>$account->getUpdatedDate(),
            )
            , true);
    }

    public function openAction()
    {
        if (!$this->isPostRequest())
        {
            return $this->jsonResult(null, false, 'please use post request');
        }
        $owner = $this->params()->fromPost('owner', '');
        $displayName = $this->params()->fromPost('displayName', '');
        $account = $this->accountService->openAccount($owner, $displayName);
        return $this->jsonResult(array('accountId'=>$account->getId()), true);
    }

    public function getBalanceAction()
    {
        $id = $this->params()->fromQuery('id', '');
        $balance = $this->accountService->getAccountBalance($id);
        return $this->jsonResult($balance, true);
    }

    public function closeAction()
    {
        if (!$this->isPostRequest())
        {
            return $this->jsonResult(null, false, 'please use post request');
        }
        $id = $this->params()->fromPost('id');
        $this->accountService->closeAccount($id);
        return $this->jsonResult(null, true);
    }
}