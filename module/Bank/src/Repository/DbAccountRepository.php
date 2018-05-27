<?php
namespace Bank\Repository;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Bank\Model\Account;

class DbAccountRepository implements AccountRepositoryInterface
{
    private $accountTableGateway;

    public function __construct(TableGatewayInterface $accountTableGateway)
    {
        $this->accountTableGateway = $accountTableGateway;
    }

    public function getAccount($id)
    {
        $id = (int) $id;
        $rowset = $this->accountTableGateway->select(['id' => $id, 'isDeleted'=> false]);
        $row = $rowset->current();
        if (! $row) {
            return null;
        }

        return $row;
    }

    public function saveAccount(Account $account)
    {
        $data = [
                    'owner' => $account->getOwner(),
                    'displayName'  => $account->getDisplayName(),
                    'balance' => $account->getBalance(),
                    'createdDate' => $account->getCreatedDate(),
                    'updatedDate' => date('Y-m-d G:i:s'),
                    'isDeleted' => $account->getIsDeleted()
                ];

        $id = (int) $account->getId();

        if ($id === 0) {
            $this->accountTableGateway->insert($data);
            $account->setId($this->accountTableGateway->lastInsertValue);
            return $account;
        }

        if (! $this->getAccount($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update account with identifier %d; does not exist',
                $id
            ));
        }

        $this->accountTableGateway->update($data, ['id' => $id]);
        return $account;
    }

    public function deleteAccount($id)
    {
        $account = $this->getAccount($id);

        if (! $account) {
            throw new RuntimeException(sprintf(
                'Cannot delete account with identifier %d; does not exist',
                $id
            ));
        }

        $data = [
            'owner' => $account->getOwner(),
            'displayName'  => $account->getDisplayName(),
            'balance' => $account->getBalance(),
            'createdDate' => $account->getCreatedDate(),
            'updatedDate' => date('Y-m-d G:i:s'),
            'isDeleted' => true
        ];

        $id = (int) $account->getId();

        $this->accountTableGateway->update($data, ['id' => $id]);
    }
}