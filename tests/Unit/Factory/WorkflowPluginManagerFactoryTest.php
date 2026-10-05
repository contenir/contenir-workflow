<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Factory;

use Contenir\Workflow\Factory\WorkflowPluginManagerFactory;
use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use Contenir\Workflow\Workflow\PageWorkflow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class WorkflowPluginManagerFactoryTest extends TestCase
{
    #[Test]
    public function buildsAPluginManagerThatProvidesThePageWorkflow(): void
    {
        $manager = (new WorkflowPluginManagerFactory())(new InMemoryContainer());

        static::assertInstanceOf(PageWorkflow::class, $manager->get('PageWorkflow'));
    }
}
