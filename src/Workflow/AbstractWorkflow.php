<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Contenir\Workflow\ResourceInterface;

/**
 * Abstract base class for workflow implementations
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

    /** @var array<string> */
    protected array $methods = ['GET'];

    public function setResource(ResourceInterface $resource): void
    {
        $this->resource = $resource;
        $this->workflowId = sprintf('%s-%s', $resource->getType(), $resource->getId());
    }

    public function getResource(): ResourceInterface
    {
        if (!$this->resource) {
            throw new \RuntimeException('Resource not set on workflow');
        }

        return $this->resource;
    }

    public function getRouteId(): string
    {
        if (!$this->resource) {
            throw new \RuntimeException('Resource not set on workflow');
        }

        return sprintf('%s-%s', $this->resource->getType(), $this->resource->getId());
    }

    public function getRoutePath(): string
    {
        if ($this->routePath) {
            return $this->routePath;
        }

        $slug = $this->getResource()->getSlug();
        return '/' . implode('/', array_filter(explode('/', $slug)));
    }

    public function getRouteMiddleware(): string
    {
        if (!$this->middleware) {
            throw new \RuntimeException('Middleware not configured for workflow');
        }

        return $this->middleware;
    }

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
     * Must be implemented by concrete workflow classes
     */
    abstract public function getRouteConfig(): ?array;
}
