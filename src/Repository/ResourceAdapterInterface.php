<?php

declare(strict_types=1);

namespace Contenir\Workflow\Repository;

use Contenir\Workflow\ResourceInterface;

/**
 * Adapter interface for fetching resources from a data source
 */
interface ResourceAdapterInterface
{
    /**
     * Get all top-level resources with their children loaded
     *
     * @return iterable<ResourceInterface>
     */
    public function getWorkflowResources(): iterable;
}
