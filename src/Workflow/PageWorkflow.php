<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Override;
use RuntimeException;

use function is_string;
use function method_exists;

/**
 * Workflow for simple page routes.
 *
 * The middleware comes from the resource's own getMiddleware() method when it
 * has one, otherwise from the workflow's $middleware property. A resource
 * without middleware gets a navigation page but no route.
 *
 * @psalm-import-type RouteConfig from WorkflowInterface
 *
 * @api
 */
class PageWorkflow extends AbstractWorkflow
{
    protected string $changeFrequency = 'monthly';

    protected string $priority = '0.6';

    /**
     * @return RouteConfig|null
     *
     * @throws RuntimeException When no resource has been set.
     *
     * @mago-expect analysis:mixed-assignment A resource's own getMiddleware() is untyped; the result is checked.
     */
    #[Override]
    public function getRouteConfig(): ?array
    {
        $resource   = $this->getResource();
        $middleware = method_exists($resource, 'getMiddleware') ? $resource->getMiddleware() : $this->middleware;

        if (! is_string($middleware) || '' === $middleware) {
            return null;
        }

        return [
            'path'       => $this->getRoutePath(),
            'middleware' => $middleware,
            'methods'    => $this->methods,
            'name'       => $this->getRouteId(),
            'options'    => [
                'defaults' => [
                    'id' => $resource->getPrimaryKeys(),
                ],
            ],
        ];
    }
}
