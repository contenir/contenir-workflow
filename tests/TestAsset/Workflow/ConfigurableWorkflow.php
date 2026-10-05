<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Workflow;

use Contenir\Workflow\Workflow\AbstractWorkflow;
use Override;

/**
 * A concrete AbstractWorkflow whose protected settings are set through the
 * constructor, and which returns a fixed route config.
 *
 * @psalm-import-type RouteConfig from \Contenir\Workflow\Workflow\WorkflowInterface
 */
final class ConfigurableWorkflow extends AbstractWorkflow
{
    /**
     * @param RouteConfig|null $routeConfig
     */
    public function __construct(
        private readonly ?array $routeConfig = null,
        ?string $routePath = null,
        ?string $routeTitle = null,
        ?string $workflowTitle = null,
        ?string $middleware = null,
    ) {
        $this->routePath     = $routePath;
        $this->routeTitle    = $routeTitle;
        $this->workflowTitle = $workflowTitle;
        $this->middleware    = $middleware;
    }

    #[Override]
    public function getRouteConfig(): ?array
    {
        return $this->routeConfig;
    }
}
