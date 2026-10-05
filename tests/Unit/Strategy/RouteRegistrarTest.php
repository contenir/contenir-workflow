<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Strategy;

use Contenir\Workflow\Strategy\RouteRegistrar;
use Mezzio\Application;
use Mezzio\Router\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

#[Group('unit')]
final class RouteRegistrarTest extends TestCase
{
    #[Test]
    public function appliesTheRouteOptionsSoDefaultsReachTheRequest(): void
    {
        $route = $this->route();
        $app   = $this->createStub(Application::class);
        $app->method('route')->willReturn($route);

        RouteRegistrar::register($app, [
            'page-1' => [
                'path'       => '/about',
                'middleware' => 'page.handler',
                'methods'    => ['GET'],
                'name'       => 'page-1',
                'options'    => ['defaults' => ['id' => ['page_id' => 1]]],
            ],
        ]);

        static::assertSame(['defaults' => ['id' => ['page_id' => 1]]], $route->getOptions());
    }

    #[Test]
    public function leavesTheOptionsAloneWhenTheRouteHasNone(): void
    {
        $route = $this->route();
        $route->setOptions(['existing' => true]);
        $app = $this->createStub(Application::class);
        $app->method('route')->willReturn($route);

        RouteRegistrar::register($app, [
            'page-1' => [
                'path'       => '/about',
                'middleware' => 'page.handler',
                'methods'    => ['POST'],
                'name'       => 'page-1',
            ],
        ]);

        static::assertSame(['existing' => true], $route->getOptions());
    }

    #[Test]
    public function letsMezzioNameARouteWithAnEmptyKey(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->once())
            ->method('route')
            ->with('/', 'home.handler', ['GET'], null)
            ->willReturn($this->route());

        RouteRegistrar::register($app, [
            '' => [
                'path'       => '/',
                'middleware' => 'home.handler',
                'methods'    => ['GET'],
                'name'       => 'home',
            ],
        ]);
    }

    #[Test]
    public function passesTheRouteNameMethodsAndMiddlewareToTheApplication(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->once())
            ->method('route')
            ->with('/contact', 'contact.handler', ['GET', 'POST'], 'page-2')
            ->willReturn($this->route());

        RouteRegistrar::register($app, [
            'page-2' => [
                'path'       => '/contact',
                'middleware' => 'contact.handler',
                'methods'    => ['GET', 'POST'],
                'name'       => 'page-2',
            ],
        ]);
    }

    private function route(): Route
    {
        return new Route('/about', $this->createStub(MiddlewareInterface::class), ['GET'], 'page-1');
    }
}
