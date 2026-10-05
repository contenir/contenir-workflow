<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Strategy;

use Contenir\Workflow\ResourceInterface;
use Contenir\Workflow\Strategy\AbstractResourceStrategy;
use Override;

/**
 * A custom strategy that picks the workflow plugin by resource type.
 */
final class TypedResourceStrategy extends AbstractResourceStrategy
{
    #[Override]
    protected function getWorkflowType(ResourceInterface $resource): string
    {
        return "{$resource->getType()}-workflow";
    }
}
