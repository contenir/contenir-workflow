<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Contenir\Workflow\ResourceInterface;
use Override;
use RuntimeException;

use function array_filter;
use function explode;
use function implode;
use function sprintf;

/**
 * Base class for workflow implementations.
 *
 * @psalm-import-type RouteConfig from WorkflowInterface
 * @psalm-import-type NavigationConfig from WorkflowInterface
 *
 * @api
 */
abstract class AbstractWorkflow implements WorkflowInterface
{
    protected ?ResourceInterface $resource = null;

    protected ?string $workflowId = null;

    protected ?string $workflowTitle = null;

    protected ?string $workflowDescription = null;

    protected ?string $routePath = null;

    protected ?string $routeTitle = null;

    protected ?string $middleware = null;

    protected string $changeFrequency = 'weekly';

    protected string $priority = '0.5';

    /** @var list<string> */
    protected array $methods = ['GET'];

    /**
     * Must be implemented by concrete workflow classes.
     *
     * @return RouteConfig|null
     */
    #[Override]
    abstract public function getRouteConfig(): ?array;

    /**
     * @return NavigationConfig
     *
     * @throws RuntimeException When no resource has been set.
     */
    #[Override]
    public function getNavigationConfig(): array
    {
        return [
            'label'      => $this->routeTitle ?? $this->workflowTitle ?? 'Untitled',
            'route'      => $this->getRouteId(),
            'changefreq' => $this->changeFrequency,
            'priority'   => $this->priority,
            'visible'    => true,
            'pages'      => [],
        ];
    }

    /**
     * @throws RuntimeException When no resource has been set.
     */
    #[Override]
    public function getResource(): ResourceInterface
    {
        if (null === $this->resource) {
            throw new RuntimeException('Resource not set on workflow');
        }

        return $this->resource;
    }

    /**
     * @return non-empty-string
     *
     * @throws RuntimeException When no resource has been set.
     */
    #[Override]
    public function getRouteId(): string
    {
        $resource = $this->getResource();

        return sprintf('%s-%s', $resource->getType(), $resource->getId());
    }

    /**
     * @throws RuntimeException When no middleware is configured.
     */
    #[Override]
    public function getRouteMiddleware(): string
    {
        if (null === $this->middleware || '' === $this->middleware) {
            throw new RuntimeException('Middleware not configured for workflow');
        }

        return $this->middleware;
    }

    /**
     * The configured route path, or the resource slug normalised to a single
     * leading slash with empty segments removed.
     *
     * @return non-empty-string
     *
     * @throws RuntimeException When no route path is configured and no resource has been set.
     */
    #[Override]
    public function getRoutePath(): string
    {
        if (null !== $this->routePath && '' !== $this->routePath) {
            return $this->routePath;
        }

        $segments = array_filter(
            explode('/', $this->getResource()->getSlug()),
            static fn(string $segment): bool => '' !== $segment,
        );

        return '/' . implode('/', $segments);
    }

    #[Override]
    public function setResource(ResourceInterface $resource): void
    {
        $this->resource   = $resource;
        $this->workflowId = sprintf('%s-%s', $resource->getType(), $resource->getId());
    }
}
