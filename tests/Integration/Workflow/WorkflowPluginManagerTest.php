<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Integration\Workflow;

use Contenir\Workflow\Workflow\PageWorkflow;
use Contenir\Workflow\Workflow\WorkflowInterface;
use Contenir\Workflow\Workflow\WorkflowPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('integration')]
final class WorkflowPluginManagerTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function pageWorkflowNames(): array
    {
        return [
            'lower-case alias' => ['page'],
            'title-case alias' => ['Page'],
            'short name'       => ['PageWorkflow'],
            'class name'       => [PageWorkflow::class],
        ];
    }

    #[Test]
    public function rejectsAPluginThatIsNotAWorkflow(): void
    {
        $manager = new WorkflowPluginManager(new ServiceManager(), [
            'invokables' => ['broken' => stdClass::class],
        ]);

        $this->expectException(InvalidServiceException::class);
        $this->expectExceptionMessage('expected an instance of type "' . WorkflowInterface::class . '"');

        $manager->get('broken');
    }

    #[Test]
    #[DataProvider('pageWorkflowNames')]
    public function resolvesThePageWorkflowByAnyOfItsNames(string $name): void
    {
        $manager = new WorkflowPluginManager(new ServiceManager());

        static::assertInstanceOf(PageWorkflow::class, $manager->get($name));
    }
}
