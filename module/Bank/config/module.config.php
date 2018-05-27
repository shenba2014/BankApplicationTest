<?php
namespace Bank;

use Zend\Router\Http\Segment;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'aliases' => [
            Repository\AccountRepositoryInterface::class => Repository\DbAccountRepository::class,
            Repository\TransactionRepositoryInterface::class => Repository\DbTransactionRepository::class,
            Service\TransferApprovalServiceInterface::class => Service\TransferApprovalService::class,
            Service\AccountServiceInterface::class => Service\AccountService::class,
            Service\TradingServiceInterface::class => Service\TradingService::class, 
        ],
        'factories' => [
            Repository\FakeAccountRepository::class => InvokableFactory::class,
            Repository\FakeTransactionRepository::class => InvokableFactory::class,

            Repository\AccountTableGateway::class => function ($container) {
                $dbAdapter = $container->get(AdapterInterface::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Account());
                return new TableGateway('account', $dbAdapter, null, $resultSetPrototype);
            },

            Repository\TransactionTableGateway::class => function ($container) {
                $dbAdapter = $container->get(AdapterInterface::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Model\Transaction());
                return new TableGateway('transaction', $dbAdapter, null, $resultSetPrototype);
            },
            
            Repository\DbAccountRepository::class => function ($container) {
                $accountTableGateway = $container->get(Repository\AccountTableGateway::class);
                return new Repository\DbAccountRepository($accountTableGateway);
            },

            Repository\DbTransactionRepository::class => function ($container) {
                $transactionTableGateway = $container->get(Repository\TransactionTableGateway::class);
                return new Repository\DbTransactionRepository($transactionTableGateway);
            },

            Service\TransferApprovalService::class => InvokableFactory::class,

            Service\AccountService::class => function ($container) {
                $accountRepository = $container->get(Repository\AccountRepositoryInterface::class);
                return new Service\AccountService($accountRepository);
            },

            Service\TradingService::class => function ($container) {
                $accountRepository = $container->get(Repository\AccountRepositoryInterface::class);
                $transactionRepository = $container->get(Repository\TransactionRepositoryInterface::class);
                $transferApprovalService = $container->get(Service\TransferApprovalServiceInterface::class);
                return new Service\TradingService($accountRepository, $transactionRepository, $transferApprovalService);
            },
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AccountController::class => function ($container) {
                return new Controller\AccountController($container->get(Service\AccountServiceInterface::class));
            },
            Controller\TradingController::class => function ($container) {
                return new Controller\TradingController($container->get(Service\TradingServiceInterface::class));
            },
        ],
    ],
    'router' => [
        'routes' => [
            'bank_account_get' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/account/get',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action' => 'get'
                    ],
                ],
            ],
            'bank_account_open' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/account/open',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action' => 'open'
                    ],
                ],
            ],
            'bank_account_close' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/account/close',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action' => 'close'
                    ],
                ],
            ],
            'bank_account_getbalance' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/account/balance',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action' => 'getBalance'
                    ],
                ],
            ],
            'bank_trading_withdraw' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/trading/withdraw',
                    'defaults' => [
                        'controller' => Controller\TradingController::class,
                        'action' => 'withdraw'
                    ],
                ],
            ],
            'bank_trading_deposit' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/trading/deposit',
                    'defaults' => [
                        'controller' => Controller\TradingController::class,
                        'action' => 'deposit'
                    ],
                ],
            ],
            'bank_trading_transfer' => [
                'type'    => 'literal',
                'options' => [
                    'route' => '/bank/trading/transfer',
                    'defaults' => [
                        'controller' => Controller\TradingController::class,
                        'action' => 'transfer'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ],
];