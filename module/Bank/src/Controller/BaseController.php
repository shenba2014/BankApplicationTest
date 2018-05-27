<?php
namespace Bank\Controller;

use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;

class BaseController extends AbstractActionController
{
    protected function jsonResult($data, $success, $message = null)
    {
        $result = new JsonModel(array('data' => $data, 'success' => $success, 'message' => $message));
        return $result; 
    }

    protected function isPostRequest()
    {
        return $this->getRequest()->isPost();
    }
}