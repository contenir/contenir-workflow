<?php

declare(strict_types=1);

namespace Contenir\Workflow\Workflow;

/**
 * Workflow for simple page routes: monthly, priority 0.6, routed to the
 * resource's own getMiddleware(). Extend AbstractPageWorkflow to customise.
 *
 * @api
 */
final class PageWorkflow extends AbstractPageWorkflow {}
