<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Factory;

use Contenir\Workflow\Factory\WorkflowApplicationDelegatorFactory;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use InvalidArgumentException;
use Mezzio\Application;
use Mezzio\Router\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use stdClass;

#[Group('unit')]
final class WorkflowApplicationDelegatorFactoryTest extends TestCase
{
    #[Test]
    public function registersEveryWorkflowRouteWithItsOptions(): void
    {
        $route = new Route('/about', $this->createStub(MiddlewareInterface::class), ['GET'], 'page-1');
        $app   = $this->createMock(Application::class);
        $app->expects($this->once())
            ->method('route')
            ->with('/about', 'page.handler', ['GET'], 'page-1')
            ->willReturn($route);

        $strategy = $this->createStub(ResourceStrategy::class);
        $strategy->method('getRouteConfig')
            ->willReturn([
                'page-1' => [
                    'path'       => '/about',
                    'middleware' => 'page.handler',
                    'methods'    => ['GET'],
                    'name'       => 'page-1',
                    'options'    => ['defaults' => ['id' => ['page_id' => 1]]],
                ],
            ]);

        (new WorkflowApplicationDelegatorFactory())(
            new InMemoryContainer([
                'config'   => ['workflow_manager' => ['strategy' => 'Strategy']],
                'Strategy' => $strategy,
            ]),
            Application::class,
            static fn(): Application => $app,
        );

        static::assertSame(['defaults' => ['id' => ['page_id' => 1]]], $route->getOptions());
    }

    #[Test]
    public function rejectsAMissingStrategy(): void
    {
        $app = $this->createStub(Application::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No strategy configured in workflow_manager');

        (new WorkflowApplicationDelegatorFactory())(
            new InMemoryContainer(['config' => ['workflow_manager' => []]]),
            Application::class,
            static fn(): Application => $app,
        );
    }

    #[Test]
    public function rejectsAStrategyOfTheWrongType(): void
    {
        $app = $this->createStub(Application::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "Strategy" must be a ' . ResourceStrategy::class);

        (new WorkflowApplicationDelegatorFactory())(
            new InMemoryContainer([
                'config'   => ['workflow_manager' => ['strategy' => 'Strategy']],
                'Strategy' => new stdClass(),
            ]),
            Application::class,
            static fn(): Application => $app,
        );
    }

    #[Test]
    public function returnsTheApplicationUntouchedWithoutWorkflowConfig(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->never())->method('route');

        $result = (new WorkflowApplicationDelegatorFactory())(
            new InMemoryContainer(['config' => []]),
            Application::class,
            static fn(): Application => $app,
        );

        static::assertSame($app, $result);
    }
}
