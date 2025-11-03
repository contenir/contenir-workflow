<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Psr\Container\ContainerInterface;

/**
 * Generic factory for workflow instances
 */
class WorkflowFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName
    ): WorkflowInterface {
        return new $requestedName();
    }
}
