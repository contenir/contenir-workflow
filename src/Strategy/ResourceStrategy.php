<?php

declare(strict_types=1);

namespace Contenir\Workflow\Strategy;

use Contenir\Workflow\Repository\ResourceAdapterInterface;
use Contenir\Workflow\ResourceInterface;
use Contenir\Workflow\Workflow\WorkflowInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\PluginManagerInterface;

/**
 * Strategy for building routes and navigation from database resources
 */
class ResourceStrategy
{
    /** @var array{route: array, navigation: array} */
    protected array $resources = ['route' => [], 'navigation' => []];

    /** @var array{cache_key: string, use_parent_as_landing_page: bool} */
    protected array $options;

    public function __construct(
        protected ResourceAdapterInterface $repository,
        protected PluginManagerInterface $pluginManager,
        protected StorageInterface $cache,
        array $options = []
    ) {
        $this->options = array_merge([
            'cache_key' => 'WorkflowResourceCache',
            'use_parent_as_landing_page' => false,
        ], $options);
    }

    /**
     * Get route configuration
     *
     * @return array<string, array>
     */
    public function getRouteConfig(): array
    {
        $this->build();
        return $this->resources['route'];
    }

    /**
     * Get navigation configuration
     *
     * @return array
     */
    public function getNavigationConfig(): array
    {
        $this->build();
        return $this->resources['navigation'];
    }

    /**
     * Clear the cache
     */
    public function clearCache(): void
    {
        $this->cache->removeItem($this->options['cache_key']);
    }

    /**
     * Build routes and navigation from resources (with caching)
     */
    protected function build(): void
    {
        $cacheKey = $this->options['cache_key'];

        if (!$this->cache->hasItem($cacheKey)) {
            // Cache miss - process resources
            $this->resources = ['route' => [], 'navigation' => []];
            $this->resources['navigation'] = $this->process(
                $this->repository->getWorkflowResources()
            );
            $this->cache->setItem($cacheKey, $this->resources);
        } else {
            // Cache hit - load from cache
            $this->resources = $this->cache->getItem($cacheKey);
        }
    }

    /**
     * Recursively process resources and build route/navigation structure
     *
     * @param iterable<ResourceInterface> $resources
     * @param array $pages
     * @return array
     */
    protected function process(iterable $resources, array $pages = []): array
    {
        foreach ($resources as $resource) {
            // Get workflow for this resource
            $workflow = $this->getResourceWorkflow($resource);

            // Extract route configuration
            $config = $workflow->getRouteConfig();
            $routeId = $workflow->getRouteId();

            if ($config) {
                $this->resources['route'][$routeId] = $config;
            }

            // Build navigation page
            $page = $this->getNavigationPage($workflow);

            // Recursively process children
            $children = $resource->getChildren();
            if (count($children) > 0) {
                $page['pages'] = $this->process($children, $page['pages'] ?? []);
            }

            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * Get the appropriate workflow for a resource
     */
    protected function getResourceWorkflow(ResourceInterface $resource): WorkflowInterface
    {
        // Determine workflow type from resource type
        // Default to PageWorkflow if not specified
        $workflowType = $this->getWorkflowType($resource);

        /** @var WorkflowInterface $workflow */
        $workflow = $this->pluginManager->get($workflowType);
        $workflow->setResource($resource);

        return $workflow;
    }

    /**
     * Determine workflow type from resource
     */
    protected function getWorkflowType(ResourceInterface $resource): string
    {
        // Override this method or use resource metadata to determine workflow type
        // For now, default to PageWorkflow
        return 'PageWorkflow';
    }

    /**
     * Build navigation page configuration
     */
    protected function getNavigationPage(WorkflowInterface $workflow): array
    {
        $config = $workflow->getNavigationConfig();
        $route = $workflow->getRouteId();

        $page = [
            'label'      => $config['label'],
            'route'      => $route,
            'visible'    => $config['visible'] ?? true,
            'lastmod'    => $config['lastmod'] ?? null,
            'changefreq' => $config['changefreq'] ?? 'weekly',
            'priority'   => $config['priority'] ?? '0.5',
            'pages'      => [],
        ];

        return $page;
    }
}
