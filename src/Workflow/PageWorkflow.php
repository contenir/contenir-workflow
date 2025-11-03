<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

/**
 * Workflow for simple page routes
 */
class PageWorkflow extends AbstractWorkflow
{
    protected string $changeFrequency = 'monthly';
    protected string $priority = '0.6';

    public function getRouteConfig(): ?array
    {
        // Get middleware from resource if it has the method, otherwise use configured middleware
        $middleware = $this->middleware;

        if (method_exists($this->getResource(), 'getMiddleware')) {
            $middleware = $this->getResource()->getMiddleware();
        }

        if (!$middleware) {
            // No middleware configured - skip this route
            return null;
        }

        return [
            'path'       => $this->getRoutePath(),
            'middleware' => $middleware,
            'methods'    => $this->methods,
            'name'       => $this->getRouteId(),
            'options'    => [
                'defaults' => [
                    'id' => $this->getResource()->getPrimaryKeys(),
                ],
            ],
        ];
    }
}
