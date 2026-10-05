<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Factory;

use Contenir\Workflow\Factory\WorkflowMiddlewareFactory;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use InvalidArgumentException;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('unit')]
final class WorkflowMiddlewareFactoryTest extends TestCase
{
    #[Test]
    public function buildsTheMiddlewareFromTheConfiguredStrategy(): void
    {
        $middleware = (new WorkflowMiddlewareFactory())(new InMemoryContainer([
            'config'                 => ['workflow_manager' => ['strategy' => 'Strategy']],
            Application::class       => $this->createStub(Application::class),
            'Strategy'               => $this->createStub(ResourceStrategy::class),
            MiddlewareFactory::class => $this->createStub(MiddlewareFactory::class),
        ]));

        static::assertInstanceOf(WorkflowMiddleware::class, $middleware);
    }

    #[Test]
    public function rejectsAMissingStrategy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No strategy configured in workflow_manager');

        (new WorkflowMiddlewareFactory())(new InMemoryContainer(['config' => []]));
    }

    #[Test]
    public function rejectsAStrategyOfTheWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "Strategy" must be a ' . ResourceStrategy::class);

        (new WorkflowMiddlewareFactory())(new InMemoryContainer([
            'config'                 => ['workflow_manager' => ['strategy' => 'Strategy']],
            Application::class       => $this->createStub(Application::class),
            'Strategy'               => new stdClass(),
            MiddlewareFactory::class => $this->createStub(MiddlewareFactory::class),
        ]));
    }
}
