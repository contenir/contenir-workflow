<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Workflow\WorkflowPluginManager;
use Psr\Container\ContainerInterface;

/**
 * Builds the workflow plugin manager with the application container as its
 * parent locator.
 *
 * @api
 */
final class WorkflowPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): WorkflowPluginManager
    {
        return new WorkflowPluginManager($container);
    }
}
