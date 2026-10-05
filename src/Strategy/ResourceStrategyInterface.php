<?php

declare(strict_types=1);

namespace Contenir\Workflow\Strategy;

use Contenir\Workflow\Workflow\WorkflowInterface;
use Laminas\Cache\Exception\ExceptionInterface as CacheException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Supplies the routes and navigation built from the resource tree. The route
 * registration (delegator and middleware) depends on this interface.
 *
 * @psalm-import-type RouteConfig from WorkflowInterface
 *
 * @api
 */
interface ResourceStrategyInterface
{
    /**
     * Clear any cached routes and navigation.
     *
     * @throws CacheException
     */
    public function clearCache(): void;

    /**
     * The navigation pages, one per resource, nested.
     *
     * @return list<array<string, mixed>>
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    public function getNavigationConfig(): array;

    /**
     * The route configuration, keyed by route name.
     *
     * @return array<string, RouteConfig>
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    public function getRouteConfig(): array;
}
