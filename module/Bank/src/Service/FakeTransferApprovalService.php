<?php
namespace Bank\Service;

class FakeTransferApprovalService implements TransferApprovalServiceInterface
{
    private $result = true;

    public function reject()
    {
        $this->result = false;
    }
    
    public function approve()
    {
        return $this->result;
    }
}