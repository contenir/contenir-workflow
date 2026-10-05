<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager for workflow instances. Register custom workflows on it
 * through its configuration (`setFactory()`, `setAlias()`), not by extending it.
 *
 * @extends AbstractPluginManager<WorkflowInterface>
 *
 * @api
 */
final class WorkflowPluginManager extends AbstractPluginManager
{
    protected $instanceOf = WorkflowInterface::class;

    protected $aliases = [
        'page'         => PageWorkflow::class,
        'Page'         => PageWorkflow::class,
        'PageWorkflow' => PageWorkflow::class,
    ];

    /**
     * @mago-expect analysis:invalid-property-default-value WorkflowFactory is an invokable factory class, which laminas-servicemanager accepts.
     */
    protected $factories = [
        PageWorkflow::class => WorkflowFactory::class,
    ];
}
