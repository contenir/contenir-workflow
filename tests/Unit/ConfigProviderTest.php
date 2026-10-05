<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit;

use Contenir\Workflow\ConfigProvider;
use Contenir\Workflow\Factory\ResourceStrategyFactory;
use Contenir\Workflow\Factory\WorkflowMiddlewareFactory;
use Contenir\Workflow\Factory\WorkflowPluginManagerFactory;
use Contenir\Workflow\Middleware\WorkflowMiddleware;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowPluginManager;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function var_export;

#[Group('unit')]
final class ConfigProviderTest extends TestCase
{
    #[Test]
    public function producesConfigThatCanBeCached(): void
    {
        $exported = var_export(
            value: (new ConfigProvider())(),
            return: true,
        );

        static::assertStringNotContainsString('Closure', $exported);
    }

    #[Test]
    public function registersAFactoryForEveryService(): void
    {
        static::assertSame(
            [
                'dependencies' => [
                    'factories' => [
                        ResourceStrategy::class      => ResourceStrategyFactory::class,
                        WorkflowPluginManager::class => WorkflowPluginManagerFactory::class,
                        WorkflowMiddleware::class    => WorkflowMiddlewareFactory::class,
                    ],
                ],
            ],
            (new ConfigProvider())(),
        );
    }
}
