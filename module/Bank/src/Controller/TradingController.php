<?php
namespace Bank\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Bank\Service\TradingServiceInterface;

class TradingController extends BaseController
{
    private $tradingService;

    public function __construct(TradingServiceInterface $tradingService)
    {
        $this->tradingService = $tradingService;
    }

    public function withdrawAction()
    {
        if (!$this->isPostRequest())
        {
            return $this->jsonResult(null, false, 'please use post request');
        }
        $accountId = $this->params()->fromPost('accountId','');
        $amount = (double)$this->params()->fromPost('amount',0);
        $this->tradingService->withdraw($accountId, $amount);
        return $this->jsonResult(null, true);
    }

    public function depositAction()
    {
        if (!$this->isPostRequest())
        {
            return $this->jsonResult(null, false, 'please use post request');
        }
        $accountId = $this->params()->fromPost('accountId','');
        $amount = (double)$this->params()->fromPost('amount',0);
        $this->tradingService->deposit($accountId, $amount);
        return $this->jsonResult(null, true);
    }

    public function transferAction()
    {
        if (!$this->isPostRequest())
        {
            return $this->jsonResult(null, false, 'please use post request');
        }
        $fromAccountId = $this->params()->fromPost('fromAccountId','');
        $toAccountId = $this->params()->fromPost('toAccountId','');
        $amount = (double)$this->params()->fromPost('amount',0.0);
        $this->tradingService->transfer($fromAccountId, $toAccountId, $amount);
        return $this->jsonResult(null, true);
    }
}