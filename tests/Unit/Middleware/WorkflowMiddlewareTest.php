<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Middleware;

use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategyInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Group('unit')]
final class WorkflowMiddlewareTest extends TestCase
{
    #[Test]
    public function delegatesToTheNextHandler(): void
    {
        $app = $this->createStub(Application::class);
        $app->method('route')
            ->willReturn(new Route('/about', $this->createStub(MiddlewareInterface::class), ['GET'], 'page-1'));
        $response = $this->createStub(ResponseInterface::class);
        $handler  = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $middleware = new WorkflowMiddleware($app, $this->strategy(), $this->createStub(MiddlewareFactory::class));

        static::assertSame($response, $middleware->process($this->createStub(ServerRequestInterface::class), $handler));
    }

    #[Test]
    public function registersTheRoutesOnceAcrossRequests(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->once())
            ->method('route')
            ->with('/about', 'page.handler', ['GET'], 'page-1')
            ->willReturn(new Route('/about', $this->createStub(MiddlewareInterface::class), ['GET'], 'page-1'));

        $middleware = new WorkflowMiddleware($app, $this->strategy(), $this->createStub(MiddlewareFactory::class));
        $request    = $this->createStub(ServerRequestInterface::class);

        $middleware->process($request, $this->handler());
        $middleware->process($request, $this->handler());
    }

    private function handler(): RequestHandlerInterface
    {
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createStub(ResponseInterface::class));

        return $handler;
    }

    private function strategy(): ResourceStrategyInterface
    {
        $strategy = $this->createStub(ResourceStrategyInterface::class);
        $strategy->method('getRouteConfig')
            ->willReturn([
                'page-1' => [
                    'path'       => '/about',
                    'middleware' => 'page.handler',
                    'methods'    => ['GET'],
                    'name'       => 'page-1',
                ],
            ]);

        return $strategy;
    }
}
