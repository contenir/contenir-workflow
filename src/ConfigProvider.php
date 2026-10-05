<?php

declare(strict_types=1);

namespace Contenir\Workflow;

use Contenir\Workflow\Factory\ResourceStrategyFactory;
use Contenir\Workflow\Factory\WorkflowMiddlewareFactory;
use Contenir\Workflow\Factory\WorkflowPluginManagerFactory;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowPluginManager;

/**
 * Configuration provider for contenir-workflow.
 *
 * @api
 */
final class ConfigProvider
{
    /**
     * @return array{factories: array<class-string, class-string>}
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                ResourceStrategy::class      => ResourceStrategyFactory::class,
                WorkflowPluginManager::class => WorkflowPluginManagerFactory::class,
                WorkflowMiddleware::class    => WorkflowMiddlewareFactory::class,
            ],
        ];
    }

    /**
     * @return array{dependencies: array{factories: array<class-string, class-string>}}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }
}
