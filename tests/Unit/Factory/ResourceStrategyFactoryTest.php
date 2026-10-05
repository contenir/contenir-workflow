<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Factory;

use Contenir\Workflow\Factory\ResourceStrategyFactory;
use Contenir\Workflow\Repository\ResourceAdapterInterface;
use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use Contenir\Workflow\Tests\TestAsset\Repository\InMemoryResourceAdapter;
use Contenir\Workflow\Workflow\WorkflowPluginManager;
use InvalidArgumentException;
use Laminas\Cache\Storage\StorageInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('unit')]
final class ResourceStrategyFactoryTest extends TestCase
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidConfigurations(): array
    {
        return [
            'no repository configured'   => [
                ['config' => ['workflow_manager' => []]],
                'No repository configured in workflow_manager',
            ],
            'no workflow_manager config' => [
                ['config' => []],
                'No repository configured in workflow_manager',
            ],
            'repository of wrong type'   => [
                [
                    'config' => ['workflow_manager' => ['repository' => 'Repo']],
                    'Repo'   => new stdClass(),
                ],
                'Repository must implement ' . ResourceAdapterInterface::class,
            ],
            'cache of wrong type'        => [
                [
                    'config'          => ['workflow_manager' => ['repository' => 'Repo']],
                    'Repo'            => new InMemoryResourceAdapter(),
                    'FilesystemCache' => new stdClass(),
                ],
                'Cache must implement StorageInterface',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $services
     */
    #[Test]
    #[DataProvider('invalidConfigurations')]
    public function rejectsAnInvalidConfiguration(array $services, string $message): void
    {
        $services[WorkflowPluginManager::class] = new WorkflowPluginManager(new InMemoryContainer());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        (new ResourceStrategyFactory())(new InMemoryContainer($services));
    }

    #[Test]
    public function usesTheConfiguredCacheServiceAndKey(): void
    {
        $cache = $this->createMock(StorageInterface::class);
        $cache->expects($this->once())->method('removeItem')->with('SiteRoutes');

        $strategy = (new ResourceStrategyFactory())(new InMemoryContainer([
            'config'                     => [
                'workflow_manager' => [
                    'repository'                 => 'Repo',
                    'cache'                      => 'AppCache',
                    'cache_key'                  => 'SiteRoutes',
                    'use_parent_as_landing_page' => true,
                ],
            ],
            'Repo'                       => new InMemoryResourceAdapter(),
            'AppCache'                   => $cache,
            WorkflowPluginManager::class => new WorkflowPluginManager(new InMemoryContainer()),
        ]));

        $strategy->clearCache();
    }

    #[Test]
    public function usesTheFilesystemCacheServiceAndDefaultKeyByDefault(): void
    {
        $cache = $this->createMock(StorageInterface::class);
        $cache->expects($this->once())->method('removeItem')->with('WorkflowResourceCache');

        $strategy = (new ResourceStrategyFactory())(new InMemoryContainer([
            'config'                     => ['workflow_manager' => ['repository' => 'Repo']],
            'Repo'                       => new InMemoryResourceAdapter(),
            'FilesystemCache'            => $cache,
            WorkflowPluginManager::class => new WorkflowPluginManager(new InMemoryContainer()),
        ]));

        $strategy->clearCache();
    }
}
