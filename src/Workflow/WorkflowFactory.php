<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function class_exists;
use function sprintf;

/**
 * Generic factory for workflows with a no-argument constructor.
 *
 * @api
 */
final class WorkflowFactory
{
    /**
     * @throws InvalidArgumentException When the requested name is not a workflow class.
     *
     * @mago-expect analysis:unused-parameter The factory signature passes the container.
     * @mago-expect analysis:unknown-class-instantiation The plugin manager supplies the class name; the result is checked.
     */
    public function __invoke(ContainerInterface $container, string $requestedName): WorkflowInterface
    {
        if (! class_exists($requestedName)) {
            throw new InvalidArgumentException(sprintf('Workflow class "%s" does not exist', $requestedName));
        }

        $workflow = new $requestedName();
        if (! $workflow instanceof WorkflowInterface) {
            throw new InvalidArgumentException(sprintf(
                'Workflow class "%s" must implement %s',
                $requestedName,
                WorkflowInterface::class,
            ));
        }

        return $workflow;
    }
}
