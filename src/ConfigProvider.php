<?php

declare(strict_types=1);

namespace Contenir\Workflow;

use Contenir\Workflow\Factory\ResourceStrategyFactory;
use Contenir\Workflow\Factory\WorkflowMiddlewareFactory;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowPluginManager;

/**
 * Configuration provider for contenir-workflow
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                ResourceStrategy::class => ResourceStrategyFactory::class,
                WorkflowPluginManager::class => function ($container) {
                    return new WorkflowPluginManager($container);
                },
                WorkflowMiddleware::class => WorkflowMiddlewareFactory::class,
            ],
        ];
    }
}
