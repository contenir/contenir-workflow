<?php

declare(strict_types=1);

namespace Contenir\Workflow\Factory;

use Contenir\Workflow\Strategy\ResourceStrategy;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

/**
 * Delegator factory that registers workflow-generated routes with the Application
 */
class WorkflowApplicationDelegatorFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback
    ): Application {
        /** @var Application $app */
        $app = $callback();

        // Get configuration
        $config = $container->get('config');

        if (!isset($config['workflow_manager'])) {
            // No workflow configuration - return app as-is
            return $app;
        }

        $workflowConfig = $config['workflow_manager'];

        if (!isset($workflowConfig['strategy'])) {
            throw new \InvalidArgumentException('No strategy configured in workflow_manager');
        }

        // Get strategy and routes
        /** @var ResourceStrategy $strategy */
        $strategy = $container->get($workflowConfig['strategy']);
        $routes = $strategy->getRouteConfig();

        // Register each route with Mezzio
        foreach ($routes as $routeName => $routeConfig) {
            $app->route(
                $routeConfig['path'],
                $routeConfig['middleware'],
                $routeConfig['methods'] ?? ['GET'],
                $routeName
            );
        }

        return $app;
    }
}
