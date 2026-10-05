<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Resource;

use Contenir\Workflow\ResourceInterface;

/**
 * A resource that names its own middleware, as PageWorkflow looks for.
 */
final class MiddlewareResource extends FakeResource
{
    /**
     * @param iterable<ResourceInterface> $children
     */
    public function __construct(
        private readonly mixed $middleware,
        string $slug = 'about',
        int $id = 1,
        iterable $children = [],
    ) {
        parent::__construct($slug, $id, 'page', $children, ['page_id' => $id]);
    }

    public function getMiddleware(): mixed
    {
        return $this->middleware;
    }
}
