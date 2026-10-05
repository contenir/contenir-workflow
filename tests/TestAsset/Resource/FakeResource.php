<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Resource;

use Contenir\Workflow\ResourceInterface;
use Override;

/**
 * A resource with fixed values.
 */
class FakeResource implements ResourceInterface
{
    /**
     * @param iterable<ResourceInterface> $children
     * @param array<string, mixed> $primaryKeys
     */
    public function __construct(
        private readonly string $slug = 'about',
        private readonly int|string $id = 1,
        private readonly string $type = 'page',
        private readonly iterable $children = [],
        private readonly array $primaryKeys = ['page_id' => 1],
    ) {}

    #[Override]
    public function getChildren(): iterable
    {
        return $this->children;
    }

    #[Override]
    public function getId(): int|string
    {
        return $this->id;
    }

    #[Override]
    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    #[Override]
    public function getSlug(): string
    {
        return $this->slug;
    }

    #[Override]
    public function getType(): string
    {
        return $this->type;
    }
}
