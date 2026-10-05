<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Container\WorkflowConfig;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use InvalidArgumentException;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

/**
 * Builds the WorkflowMiddleware from the "workflow_manager.strategy" service.
 *
 * @api
 */
final class WorkflowMiddlewareFactory
{
    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException When the service is not a $type.
     *
     * @mago-expect analysis:mixed-assignment Container services are untyped; the type is checked here.
     */
    private static function service(ContainerInterface $container, string $name, string $type): object
    {
        $service = $container->get($name);
        if (! $service instanceof $type) {
            throw new InvalidArgumentException(sprintf('Service "%s" must be a %s', $name, $type));
        }

        return $service;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException When no strategy is configured or a service has the wrong type.
     */
    public function __invoke(ContainerInterface $container): WorkflowMiddleware
    {
        $strategyName = WorkflowConfig::fromContainerOrEmpty($container)->requiredString('strategy');

        return new WorkflowMiddleware(
            self::service($container, Application::class, Application::class),
            self::service($container, $strategyName, ResourceStrategy::class),
            self::service($container, MiddlewareFactory::class, MiddlewareFactory::class),
        );
    }
}
