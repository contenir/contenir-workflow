<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Workflow;

use Contenir\Workflow\Workflow\AbstractPageWorkflow;

/**
 * A custom page workflow that only changes the sitemap settings.
 */
final class DailyPageWorkflow extends AbstractPageWorkflow
{
    protected string $changeFrequency = 'daily';

    protected string $priority = '0.9';
}
