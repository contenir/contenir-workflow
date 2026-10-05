<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Resource;

use Contenir\Workflow\ResourceInterface;
use Generator;

/**
 * Builds resource trees for tests.
 */
final class ResourceFactory
{
    /**
     * Yields the given resources, so children are a non-countable Traversable.
     *
     * @param list<ResourceInterface> $resources
     *
     * @return Generator<int, ResourceInterface>
     */
    public static function generate(array $resources): Generator
    {
        yield from $resources;
    }

    /**
     * A page with the given middleware and children.
     *
     * @param iterable<ResourceInterface> $children
     */
    public static function page(
        int $id,
        string $slug,
        string $middleware = 'page.handler',
        iterable $children = [],
    ): ResourceInterface {
        return new MiddlewareResource($middleware, $slug, $id, $children);
    }
}
