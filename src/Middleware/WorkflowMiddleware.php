<?php

declare(strict_types=1);

namespace Contenir\Workflow\Middleware;

use Contenir\Workflow\Strategy\ResourceStrategy;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that registers workflow-generated routes with the Application
 *
 * This should be added early in the pipeline to ensure workflow routes
 * are registered before routing occurs.
 */
class WorkflowMiddleware implements MiddlewareInterface
{
    private bool $routesRegistered = false;

    public function __construct(
        private readonly Application $app,
        private readonly ResourceStrategy $strategy,
        private readonly MiddlewareFactory $factory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Register routes only once
        if (!$this->routesRegistered) {
            $this->registerWorkflowRoutes();
            $this->routesRegistered = true;
        }

        // Continue to next middleware
        return $handler->handle($request);
    }

    private function registerWorkflowRoutes(): void
    {
        $routes = $this->strategy->getRouteConfig();

        // Register each route with Mezzio
        foreach ($routes as $routeName => $routeConfig) {
            $this->app->route(
                $routeConfig['path'],
                $routeConfig['middleware'],
                $routeConfig['methods'] ?? ['GET'],
                $routeName
            );
        }
    }
}
