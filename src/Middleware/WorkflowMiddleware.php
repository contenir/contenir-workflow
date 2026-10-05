<?php

declare(strict_types=1);

namespace Contenir\Workflow\Middleware;

use Contenir\Workflow\Strategy\ResourceStrategyInterface;
use Contenir\Workflow\Strategy\RouteRegistrar;
use Laminas\Cache\Exception\ExceptionInterface as CacheException;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Registers workflow-generated routes with the Application on the first
 * request it sees, then delegates.
 *
 * Pipe it before the routing middleware so the routes exist when routing
 * runs.
 *
 * @api
 */
final class WorkflowMiddleware implements MiddlewareInterface
{
    private bool $routesRegistered = false;

    /**
     * @mago-expect analysis:unused-property Kept for constructor compatibility with 0.x.
     */
    public function __construct(
        private readonly Application $app,
        private readonly ResourceStrategyInterface $strategy,
        private readonly MiddlewareFactory $factory,
    ) {}

    /**
     * @throws CacheException
     * @throws ContainerExceptionInterface When a workflow cannot be created.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->routesRegistered) {
            RouteRegistrar::register($this->app, $this->strategy->getRouteConfig());
            $this->routesRegistered = true;
        }

        return $handler->handle($request);
    }
}
