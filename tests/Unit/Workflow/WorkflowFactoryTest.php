<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Workflow;

use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use Contenir\Workflow\Workflow\PageWorkflow;
use Contenir\Workflow\Workflow\WorkflowFactory;
use Contenir\Workflow\Workflow\WorkflowInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('unit')]
final class WorkflowFactoryTest extends TestCase
{
    #[Test]
    public function createsTheRequestedWorkflow(): void
    {
        static::assertInstanceOf(
            PageWorkflow::class,
            (new WorkflowFactory())(new InMemoryContainer(), PageWorkflow::class),
        );
    }

    #[Test]
    public function rejectsAClassThatIsNotAWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow class "stdClass" must implement ' . WorkflowInterface::class);

        (new WorkflowFactory())(new InMemoryContainer(), stdClass::class);
    }

    #[Test]
    public function rejectsAnUnknownClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow class "missing.workflow" does not exist');

        (new WorkflowFactory())(new InMemoryContainer(), requestedName: 'missing.workflow');
    }
}
