<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Repository\ResourceAdapterInterface;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowPluginManager;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

class ResourceStrategyFactory
{
    public function __invoke(ContainerInterface $container): ResourceStrategy
    {
        $config = $container->get('config');
        $workflowConfig = $config['workflow_manager'] ?? [];

        // Get repository adapter
        if (!isset($workflowConfig['repository'])) {
            throw new \InvalidArgumentException('No repository configured in workflow_manager');
        }

        $repository = $container->get($workflowConfig['repository']);

        if (!$repository instanceof ResourceAdapterInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Repository must implement %s',
                ResourceAdapterInterface::class
            ));
        }

        // Get workflow plugin manager
        $pluginManager = $container->get(WorkflowPluginManager::class);

        // Get cache
        $cacheService = $workflowConfig['cache'] ?? 'FilesystemCache';
        $cache = $container->get($cacheService);

        if (!$cache instanceof StorageInterface) {
            throw new \InvalidArgumentException('Cache must implement StorageInterface');
        }

        // Strategy options
        $options = [
            'cache_key' => $workflowConfig['cache_key'] ?? 'WorkflowResourceCache',
            'use_parent_as_landing_page' => $workflowConfig['use_parent_as_landing_page'] ?? false,
        ];

        return new ResourceStrategy($repository, $pluginManager, $cache, $options);
    }
}
