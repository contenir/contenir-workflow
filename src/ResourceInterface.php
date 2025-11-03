<?php

declare(strict_types=1);

namespace Contenir\Workflow;

/**
 * Interface for resources that can be converted to workflows
 */
interface ResourceInterface
{
    /**
     * Get the URL slug for this resource
     */
    public function getSlug(): string;

    /**
     * Get the primary key(s) for this resource
     *
     * @return array<string, mixed>
     */
    public function getPrimaryKeys(): array;

    /**
     * Get the resource type (e.g., 'page', 'article')
     */
    public function getType(): string;

    /**
     * Get the resource ID
     */
    public function getId(): int|string;

    /**
     * Get child resources (for hierarchical structures)
     *
     * @return iterable<ResourceInterface>
     */
    public function getChildren(): iterable;
}
