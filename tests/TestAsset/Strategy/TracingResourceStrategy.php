<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\TestAsset\Strategy;

use Contenir\Workflow\ResourceInterface;
use Contenir\Workflow\Strategy\AbstractResourceStrategy;
use Contenir\Workflow\Workflow\WorkflowInterface;
use Override;

/**
 * A custom strategy that decorates every protected hook, recording each
 * call before handing over to the base implementation.
 *
 * @psalm-import-type NavigationPage from AbstractResourceStrategy
 */
final class TracingResourceStrategy extends AbstractResourceStrategy
{
    /** @var list<string> */
    public array $trace = [];

    #[Override]
    protected function build(): void
    {
        $this->trace[] = 'build';

        parent::build();
    }

    /**
     * @return NavigationPage
     */
    #[Override]
    protected function getNavigationPage(WorkflowInterface $workflow): array
    {
        $this->trace[] = "page {$workflow->getRouteId()}";

        return parent::getNavigationPage($workflow);
    }

    #[Override]
    protected function getResourceWorkflow(ResourceInterface $resource): WorkflowInterface
    {
        $workflow      = parent::getResourceWorkflow($resource);
        $this->trace[] = "workflow {$workflow->getRouteId()}";

        return $workflow;
    }

    /**
     * @param iterable<ResourceInterface> $resources
     * @param list<array<string, mixed>> $pages
     *
     * @return list<array<string, mixed>>
     */
    #[Override]
    protected function process(iterable $resources, array $pages = []): array
    {
        $this->trace[] = 'process';

        return parent::process($resources, $pages);
    }
}
