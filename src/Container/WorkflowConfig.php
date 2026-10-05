<?php

declare(strict_types=1);

namespace Contenir\Workflow\Container;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_bool;
use function is_string;
use function sprintf;

/**
 * Reads the "workflow_manager" section of the application config.
 *
 * @internal
 */
final readonly class WorkflowConfig
{
    /**
     * @param array<array-key, mixed> $values
     */
    private function __construct(
        private array $values,
    ) {}

    /**
     * The "workflow_manager" config section, or null when the application has
     * no such section.
     *
     * @throws ContainerExceptionInterface
     *
     * @mago-expect analysis:mixed-assignment The config service is untyped; its shape is checked here.
     */
    public static function fromContainer(ContainerInterface $container): ?self
    {
        $config = $container->has('config') ? $container->get('config') : [];
        if (! is_array($config) || ! is_array($config['workflow_manager'] ?? null)) {
            return null;
        }

        return new self($config['workflow_manager']);
    }

    /**
     * Like fromContainer(), but an absent section reads as empty.
     *
     * @throws ContainerExceptionInterface
     */
    public static function fromContainerOrEmpty(ContainerInterface $container): self
    {
        return self::fromContainer($container) ?? new self([]);
    }

    /**
     * @mago-expect analysis:mixed-assignment Config values are untyped; the type is checked here.
     */
    public function bool(string $key, bool $default): bool
    {
        $value = $this->values[$key] ?? null;

        return is_bool($value) ? $value : $default;
    }

    /**
     * @throws InvalidArgumentException When the key is missing or not a non-empty string.
     *
     * @mago-expect analysis:mixed-assignment Config values are untyped; the type is checked here.
     */
    public function requiredString(string $key): string
    {
        $value = $this->values[$key] ?? null;
        if (! is_string($value) || '' === $value) {
            throw new InvalidArgumentException(sprintf('No %s configured in workflow_manager', $key));
        }

        return $value;
    }

    /**
     * @mago-expect analysis:mixed-assignment Config values are untyped; the type is checked here.
     */
    public function stringOr(string $key, string $default): string
    {
        $value = $this->values[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : $default;
    }
}
