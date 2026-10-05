<?php

declare(strict_types=1);

namespace Contenir\Workflow\Strategy;

use Contenir\Workflow\Repository\ResourceAdapterInterface;
use Contenir\Workflow\ResourceInterface;
use Contenir\Workflow\Workflow\WorkflowInterface;
use InvalidArgumentException;
use Laminas\Cache\Exception\ExceptionInterface as CacheException;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use Override;
use Psr\Container\ContainerExceptionInterface;

use function is_array;
use function iterator_to_array;
use function sprintf;

/**
 * Builds routes and navigation from a tree of resources, caching both.
 *
 * The extension point for custom strategies: extend it and override the
 * protected hooks, for example getWorkflowType() to pick a workflow per
 * resource or getNavigationPage() to change the page shape. ResourceStrategy
 * is the final default implementation.
 *
 * @psalm-import-type RouteConfig from WorkflowInterface
 *
 * @psalm-type NavigationPage = array{
 *     label: string,
 *     route: string,
 *     visible: bool,
 *     lastmod: string|null,
 *     changefreq: string,
 *     priority: string,
 *     pages: list<array<string, mixed>>
 * }
 * @psalm-type Resources = array{route: array<string, RouteConfig>, navigation: list<array<string, mixed>>}
 *
 * @api
 */
abstract class AbstractResourceStrategy implements ResourceStrategyInterface
{
    private const array DEFAULT_OPTIONS = [
        'cache_key'                  => 'WorkflowResourceCache',
        'use_parent_as_landing_page' => false,
    ];

    /** @var Resources */
    protected array $resources = ['route' => [], 'navigation' => []];

    /** @var array{cache_key: string, use_parent_as_landing_page: bool} */
    protected array $options;

    /**
     * @param array{cache_key?: string, use_parent_as_landing_page?: bool} $options
     */
    public function __construct(
        protected ResourceAdapterInterface $repository,
        protected PluginManagerInterface $pluginManager,
        protected StorageInterface $cache,
        array $options = [],
    ) {
        $this->options = [...self::DEFAULT_OPTIONS, ...$options];
    }

    /**
     * @psalm-assert-if-true Resources $value
     */
    private static function isResources(mixed $value): bool
    {
        return is_array($value) && is_array($value['route'] ?? null) && is_array($value['navigation'] ?? null);
    }

    /**
     * Clear the cached routes and navigation.
     *
     * @throws CacheException
     */
    #[Override]
    public function clearCache(): void
    {
        $this->cache->removeItem($this->options['cache_key']);
    }

    /**
     * Get the navigation configuration: one page per resource, nested.
     *
     * @return list<array<string, mixed>>
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    #[Override]
    public function getNavigationConfig(): array
    {
        $this->build();

        return $this->resources['navigation'];
    }

    /**
     * Get the route configuration, keyed by route name.
     *
     * @return array<string, RouteConfig>
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    #[Override]
    public function getRouteConfig(): array
    {
        $this->build();

        return $this->resources['route'];
    }

    /**
     * Build routes and navigation from the resources, or load them from cache.
     *
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     *
     * @mago-expect analysis:mixed-assignment The cached value is checked before use.
     */
    protected function build(): void
    {
        $cacheKey = $this->options['cache_key'];

        if ($this->cache->hasItem($cacheKey)) {
            $cached = $this->cache->getItem($cacheKey);
            if (self::isResources($cached)) {
                $this->resources = $cached;

                return;
            }
        }

        $this->resources               = ['route' => [], 'navigation' => []];
        $this->resources['navigation'] = $this->process($this->repository->getWorkflowResources());
        $this->cache->setItem($cacheKey, $this->resources);
    }

    /**
     * Build the navigation page for a workflow.
     *
     * @return NavigationPage
     */
    protected function getNavigationPage(WorkflowInterface $workflow): array
    {
        $config = $workflow->getNavigationConfig();

        return [
            'label'      => $config['label'],
            'route'      => $workflow->getRouteId(),
            'visible'    => $config['visible'] ?? true,
            'lastmod'    => $config['lastmod'] ?? null,
            'changefreq' => $config['changefreq'] ?? 'weekly',
            'priority'   => $config['priority'] ?? '0.5',
            'pages'      => [],
        ];
    }

    /**
     * Get the workflow for a resource, with the resource set on it.
     *
     * @throws ContainerExceptionInterface When the workflow cannot be created.
     * @throws InvalidArgumentException When the plugin manager returns something other than a workflow.
     *
     * @mago-expect analysis:mixed-assignment Plugin managers are untyped; the type is checked here.
     */
    protected function getResourceWorkflow(ResourceInterface $resource): WorkflowInterface
    {
        $workflowType = $this->getWorkflowType($resource);
        $workflow     = $this->pluginManager->get($workflowType);

        if (! $workflow instanceof WorkflowInterface) {
            throw new InvalidArgumentException(sprintf(
                'Workflow "%s" must implement %s',
                $workflowType,
                WorkflowInterface::class,
            ));
        }

        $workflow->setResource($resource);

        return $workflow;
    }

    /**
     * Determine the workflow plugin name for a resource. Override to choose
     * per resource; the default is "PageWorkflow" for every resource.
     *
     * @mago-expect analysis:unused-parameter Subclasses choose the workflow from the resource.
     */
    protected function getWorkflowType(ResourceInterface $resource): string
    {
        return 'PageWorkflow';
    }

    /**
     * Recursively process resources, collecting routes and returning the
     * navigation pages.
     *
     * @param iterable<ResourceInterface> $resources
     * @param list<array<string, mixed>> $pages
     *
     * @return list<array<string, mixed>>
     *
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    protected function process(iterable $resources, array $pages = []): array
    {
        foreach ($resources as $resource) {
            $workflow = $this->getResourceWorkflow($resource);
            $config   = $workflow->getRouteConfig();

            if (null !== $config) {
                $this->resources['route'][$workflow->getRouteId()] = $config;
            }

            $page     = $this->getNavigationPage($workflow);
            $children = $resource->getChildren();
            $children = is_array($children) ? $children : iterator_to_array($children, preserve_keys: false);

            if ([] !== $children) {
                $page['pages'] = $this->process($children, $page['pages']);
            }

            $pages[] = $page;
        }

        return $pages;
    }
}
