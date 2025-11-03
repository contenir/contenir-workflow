<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager for workflow instances
 */
class WorkflowPluginManager extends AbstractPluginManager
{
    protected $instanceOf = WorkflowInterface::class;

    protected $aliases = [
        'page' => PageWorkflow::class,
        'Page' => PageWorkflow::class,
        'PageWorkflow' => PageWorkflow::class,
    ];

    protected $factories = [
        PageWorkflow::class => WorkflowFactory::class,
    ];
}
