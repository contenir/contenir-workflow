<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Container;

use Contenir\Workflow\Container\WorkflowConfig;
use Contenir\Workflow\Tests\TestAsset\Container\InMemoryContainer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class WorkflowConfigTest extends TestCase
{
    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function containersWithoutASection(): array
    {
        return [
            'no config service'      => [[]],
            'config is not an array' => [['config' => 'nope']],
            'no section'             => [['config' => []]],
            'section is not array'   => [['config' => ['workflow_manager' => true]]],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidStrings(): array
    {
        return [
            'missing'    => [null],
            'empty'      => [''],
            'not string' => [42],
        ];
    }

    /**
     * @param array<string, mixed> $section
     */
    private static function config(array $section): WorkflowConfig
    {
        $config = WorkflowConfig::fromContainer(new InMemoryContainer(['config' => ['workflow_manager' => $section]]));
        static::assertNotNull($config);

        return $config;
    }

    #[Test]
    #[DataProvider('invalidStrings')]
    public function fallsBackForAMissingOrInvalidString(mixed $value): void
    {
        static::assertSame('FilesystemCache', self::config(['cache' => $value])->stringOr('cache', 'FilesystemCache'));
    }

    #[Test]
    public function fallsBackForANonBool(): void
    {
        static::assertFalse(self::config(['flag' => 'yes'])->bool('flag', false));
    }

    /**
     * @param array<string, mixed> $services
     */
    #[Test]
    #[DataProvider('containersWithoutASection')]
    public function hasNoSectionWhenTheApplicationDoesNotConfigureOne(array $services): void
    {
        static::assertNull(WorkflowConfig::fromContainer(new InMemoryContainer($services)));
    }

    #[Test]
    public function readsAConfiguredBool(): void
    {
        static::assertTrue(self::config(['flag' => true])->bool('flag', false));
    }

    #[Test]
    public function readsAConfiguredString(): void
    {
        static::assertSame('Strategy', self::config(['strategy' => 'Strategy'])->requiredString('strategy'));
    }

    #[Test]
    public function readsAConfiguredStringOverTheFallback(): void
    {
        static::assertSame('AppCache', self::config(['cache' => 'AppCache'])->stringOr('cache', 'FilesystemCache'));
    }

    /**
     * @param array<string, mixed> $services
     */
    #[Test]
    #[DataProvider('containersWithoutASection')]
    public function readsAMissingSectionAsEmpty(array $services): void
    {
        $config = WorkflowConfig::fromContainerOrEmpty(new InMemoryContainer($services));

        static::assertSame('fallback', $config->stringOr('strategy', 'fallback'));
    }

    #[Test]
    #[DataProvider('invalidStrings')]
    public function rejectsAMissingOrInvalidRequiredString(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No strategy configured in workflow_manager');

        self::config(['strategy' => $value])->requiredString('strategy');
    }
}
