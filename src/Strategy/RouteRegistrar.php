<?php

declare(strict_types=1);

namespace Contenir\Workflow\Strategy;

use Contenir\Workflow\Workflow\WorkflowInterface;
use Mezzio\Application;

/**
 * Registers workflow-generated routes with a Mezzio application, including
 * each route's options (the "defaults" carrying the resource's primary keys).
 *
 * @psalm-import-type RouteConfig from WorkflowInterface
 *
 * @internal
 */
final class RouteRegistrar
{
    /**
     * @param array<string, RouteConfig> $routes
     */
    public static function register(Application $app, array $routes): void
    {
        foreach ($routes as $routeName => $routeConfig) {
            $route = $app->route(
                $routeConfig['path'],
                $routeConfig['middleware'],
                $routeConfig['methods'],
                '' === $routeName ? null : $routeName,
            );

            if (null !== ($routeConfig['options'] ?? null)) {
                $route->setOptions($routeConfig['options']);
            }
        }
    }
}
