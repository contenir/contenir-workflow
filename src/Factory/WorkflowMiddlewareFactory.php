<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

class WorkflowMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): WorkflowMiddleware
    {
        $config = $container->get('config');
        $workflowConfig = $config['workflow_manager'] ?? [];

        if (!isset($workflowConfig['strategy'])) {
            throw new \InvalidArgumentException('No strategy configured in workflow_manager');
        }

        return new WorkflowMiddleware(
            $container->get(Application::class),
            $container->get($workflowConfig['strategy']),
            $container->get(MiddlewareFactory::class)
        );
    }
}
