<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Contenir\Workflow\ResourceInterface;

/**
 * A workflow turns one resource into a route and a navigation page.
 *
 * @psalm-type RouteConfig = array{
 *     path: non-empty-string,
 *     middleware: non-empty-string,
 *     methods: list<string>,
 *     name: non-empty-string,
 *     options?: array<string, mixed>
 * }
 * @psalm-type NavigationConfig = array{
 *     label: string,
 *     route: string,
 *     visible?: bool,
 *     lastmod?: string|null,
 *     changefreq?: string,
 *     priority?: string,
 *     pages?: array<array-key, mixed>
 * }
 *
 * @api
 */
interface WorkflowInterface
{
    /**
     * Get the navigation configuration.
     *
     * @return NavigationConfig
     */
    public function getNavigationConfig(): array;

    /**
     * Get the resource this workflow operates on.
     */
    public function getResource(): ResourceInterface;

    /**
     * Get the complete route configuration for Mezzio, or null when the
     * resource has no route of its own.
     *
     * @return RouteConfig|null
     */
    public function getRouteConfig(): ?array;

    /**
     * Get the unique route identifier.
     *
     * @return non-empty-string
     */
    public function getRouteId(): string;

    /**
     * Get the middleware service name for this route.
     */
    public function getRouteMiddleware(): string;

    /**
     * Get the URL path for this workflow.
     *
     * @return non-empty-string
     */
    public function getRoutePath(): string;

    /**
     * Set the resource this workflow operates on.
     */
    public function setResource(ResourceInterface $resource): void;
}
