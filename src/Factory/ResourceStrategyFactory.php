<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Container\WorkflowConfig;
use Contenir\Workflow\Repository\ResourceAdapterInterface;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowPluginManager;
use InvalidArgumentException;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

/**
 * Builds the ResourceStrategy from the "workflow_manager" config: the
 * "repository" service (required), the "cache" storage service (default
 * "FilesystemCache"), "cache_key" and "use_parent_as_landing_page".
 *
 * @api
 */
final class ResourceStrategyFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException When the repository or cache is missing or of the wrong type.
     *
     * @mago-expect analysis:mixed-assignment Container services are untyped; the types are checked here.
     */
    public function __invoke(ContainerInterface $container): ResourceStrategy
    {
        $config     = WorkflowConfig::fromContainerOrEmpty($container);
        $repository = $container->get($config->requiredString('repository'));

        if (! $repository instanceof ResourceAdapterInterface) {
            throw new InvalidArgumentException(sprintf(
                'Repository must implement %s',
                ResourceAdapterInterface::class,
            ));
        }

        $cache = $container->get($config->stringOr('cache', 'FilesystemCache'));
        if (! $cache instanceof StorageInterface) {
            throw new InvalidArgumentException('Cache must implement StorageInterface');
        }

        return new ResourceStrategy($repository, $container->get(WorkflowPluginManager::class), $cache, [
            'cache_key'                  => $config->stringOr('cache_key', 'WorkflowResourceCache'),
            'use_parent_as_landing_page' => $config->bool('use_parent_as_landing_page', false),
        ]);
    }
}
