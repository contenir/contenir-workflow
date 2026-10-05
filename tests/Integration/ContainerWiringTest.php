<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Integration;

use Contenir\Workflow\ConfigProvider;
use Contenir\Workflow\Factory\WorkflowApplicationDelegatorFactory;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Tests\TestAsset\Repository\InMemoryResourceAdapter;
use Contenir\Workflow\Tests\TestAsset\Resource\ResourceFactory;
use Contenir\Workflow\Tests\TestAsset\Router\RecordingRouter;
use Contenir\Workflow\Tests\Trait\InMemoryCacheTrait;
use Laminas\HttpHandlerRunner\RequestHandlerRunnerInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Application;
use Mezzio\MiddlewareContainer;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteCollector;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_keys;

/**
 * The ConfigProvider's services in a real ServiceManager, registering routes
 * with a real Mezzio Application and route collector.
 */
#[Group('integration')]
final class ContainerWiringTest extends TestCase
{
    use InMemoryCacheTrait;

    private RecordingRouter $router;

    private ServiceManager $container;

    #[Test]
    public function buildsTheStrategyFromConfiguration(): void
    {
        $strategy = $this->container->get(ResourceStrategy::class);

        static::assertSame(['page-1', 'page-2'], array_keys($strategy->getRouteConfig()));
    }

    #[Test]
    public function registersRoutesThroughTheMiddlewareOnTheFirstRequest(): void
    {
        $middleware = $this->container->get(WorkflowMiddleware::class);
        $handler    = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createStub(ResponseInterface::class));

        $middleware->process($this->createStub(ServerRequestInterface::class), $handler);

        static::assertSame('/about/team', $this->router->routes['page-2']->getPath());
    }

    #[Test]
    public function registersRoutesWithTheirDefaultsThroughTheDelegator(): void
    {
        $this->container->addDelegator(Application::class, WorkflowApplicationDelegatorFactory::class);

        $this->container->get(Application::class);

        static::assertSame(['page-1', 'page-2'], array_keys($this->router->routes));
        static::assertSame(
            ['defaults' => ['id' => ['page_id' => 2]]],
            $this->router->routes['page-2']->getOptions(),
        );
    }

    protected function setUp(): void
    {
        $this->router    = new RecordingRouter();
        $this->container = $this->createContainer();
    }

    private function createContainer(): ServiceManager
    {
        $container = new ServiceManager((new ConfigProvider())->getDependencies());
        $container->setService('config', [
            'workflow_manager' => [
                'strategy'   => ResourceStrategy::class,
                'repository' => InMemoryResourceAdapter::class,
                'cache'      => 'WorkflowCache',
            ],
        ]);
        $container->setService(InMemoryResourceAdapter::class, new InMemoryResourceAdapter([
            ResourceFactory::page(1, 'about', children: [ResourceFactory::page(2, 'about/team')]),
        ]));
        $container->setService('WorkflowCache', $this->createInMemoryCache());

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container));
        $container->setService(MiddlewareFactory::class, $middlewareFactory);
        $container->setFactory(Application::class, fn(): Application => new Application(
            $middlewareFactory,
            new MiddlewarePipe(),
            new RouteCollector($this->router),
            $this->createStub(RequestHandlerRunnerInterface::class),
        ));

        return $container;
    }
}
