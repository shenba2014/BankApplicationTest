<?php
namespace Bank\Service;

class TransferApprovalService implements TransferApprovalServiceInterface
{
    public function approve()
    {
        $json =
         file_get_contents('http://handy.travel/test/success.json/');
        $obj = json_decode($json);
        return $obj->{'status'} == "success";
    }
}