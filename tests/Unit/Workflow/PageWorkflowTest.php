<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Workflow;

use Contenir\Workflow\Tests\TestAsset\Resource\FakeResource;
use Contenir\Workflow\Tests\TestAsset\Resource\MiddlewareResource;
use Contenir\Workflow\Workflow\PageWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PageWorkflowTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableMiddleware(): array
    {
        return [
            'null'       => [null],
            'empty'      => [''],
            'not string' => [['app.handler']],
        ];
    }

    #[Test]
    public function describesPagesAsMonthlyWithRaisedPriority(): void
    {
        $workflow = new PageWorkflow();
        $workflow->setResource(new FakeResource());

        $navigation = $workflow->getNavigationConfig();

        static::assertSame(['monthly', '0.6'], [$navigation['changefreq'], $navigation['priority']]);
    }

    #[Test]
    public function hasNoRouteForAResourceWithoutMiddlewareByDefault(): void
    {
        $workflow = new PageWorkflow();
        $workflow->setResource(new FakeResource());

        static::assertNull($workflow->getRouteConfig());
    }

    #[Test]
    #[DataProvider('unusableMiddleware')]
    public function hasNoRouteWhenTheResourceNamesNoUsableMiddleware(mixed $middleware): void
    {
        $workflow = new PageWorkflow();
        $workflow->setResource(new MiddlewareResource($middleware));

        static::assertNull($workflow->getRouteConfig());
    }

    #[Test]
    public function routesToTheMiddlewareTheResourceNames(): void
    {
        $workflow = new PageWorkflow();
        $workflow->setResource(new MiddlewareResource('page.handler', 'about/team', 5));

        static::assertSame(
            [
                'path'       => '/about/team',
                'middleware' => 'page.handler',
                'methods'    => ['GET'],
                'name'       => 'page-5',
                'options'    => ['defaults' => ['id' => ['page_id' => 5]]],
            ],
            $workflow->getRouteConfig(),
        );
    }
}
