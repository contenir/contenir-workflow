<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Container\WorkflowConfig;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Strategy\RouteRegistrar;
use InvalidArgumentException;
use Laminas\Cache\Exception\ExceptionInterface as CacheException;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

/**
 * Delegator factory for Mezzio\Application that registers the
 * workflow-generated routes when the application is created. Does nothing
 * when there is no "workflow_manager" config.
 *
 * @api
 */
final class WorkflowApplicationDelegatorFactory
{
    /**
     * @param callable(): Application $callback
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException When no strategy is configured or it is not a ResourceStrategy.
     *
     * @mago-expect analysis:mixed-assignment Container services are untyped; the type is checked here.
     * @mago-expect analysis:unused-parameter The delegator signature passes the service name.
     */
    public function __invoke(ContainerInterface $container, string $name, callable $callback): Application
    {
        $app    = $callback();
        $config = WorkflowConfig::fromContainer($container);

        if (null === $config) {
            return $app;
        }

        $strategyName = $config->requiredString('strategy');
        $strategy     = $container->get($strategyName);
        if (! $strategy instanceof ResourceStrategy) {
            throw new InvalidArgumentException(sprintf(
                'Service "%s" must be a %s',
                $strategyName,
                ResourceStrategy::class,
            ));
        }

        RouteRegistrar::register($app, $strategy->getRouteConfig());

        return $app;
    }
}
