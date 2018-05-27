<?php
namespace Bank;

use Zend\Mvc\Event;
use Zend\View\Model\JsonModel;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        $application = $e->getApplication();
        $em = $application->getEventManager();
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'));
    }

    public function handleError(\Zend\Mvc\MvcEvent $e)
    {
        $exception = $e->getParam('exception');
        if ($exception)
        {
            $e->setResult(
            new JsonModel(array('data' => null, 'success' => false, 'message' => $exception->getMessage())));
        }
    }
}