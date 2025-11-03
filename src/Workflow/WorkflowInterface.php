<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Contenir\Workflow\ResourceInterface;

/**
 * Interface for workflow implementations
 */
interface WorkflowInterface
{
    /**
     * Set the resource this workflow operates on
     */
    public function setResource(ResourceInterface $resource): void;

    /**
     * Get the resource this workflow operates on
     */
    public function getResource(): ResourceInterface;

    /**
     * Get unique route identifier
     */
    public function getRouteId(): string;

    /**
     * Get the URL path for this workflow
     */
    public function getRoutePath(): string;

    /**
     * Get the middleware handler for this route
     */
    public function getRouteMiddleware(): string;

    /**
     * Get complete route configuration for Mezzio
     *
     * @return array{
     *     path: string,
     *     middleware: string,
     *     methods: array<string>,
     *     name: string,
     *     options?: array<string, mixed>
     * }|null
     */
    public function getRouteConfig(): ?array;

    /**
     * Get navigation configuration
     *
     * @return array{
     *     label: string,
     *     route: string,
     *     visible?: bool,
     *     lastmod?: string,
     *     changefreq?: string,
     *     priority?: string,
     *     pages?: array
     * }
     */
    public function getNavigationConfig(): array;
}
