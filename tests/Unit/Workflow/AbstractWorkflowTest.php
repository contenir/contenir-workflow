<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Workflow;

use Contenir\Workflow\Tests\TestAsset\Resource\FakeResource;
use Contenir\Workflow\Tests\TestAsset\Workflow\ConfigurableWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[Group('unit')]
final class AbstractWorkflowTest extends TestCase
{
    /**
     * @return array<string, array{string|null, string|null, string}>
     */
    public static function labels(): array
    {
        return [
            'route title wins'        => ['Route', 'Workflow', 'Route'],
            'workflow title fallback' => [null, 'Workflow', 'Workflow'],
            'untitled fallback'       => [null, null, 'Untitled'],
        ];
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function missingMiddleware(): array
    {
        return [
            'null'  => [null],
            'empty' => [''],
        ];
    }

    /**
     * @return array<string, array{callable(ConfigurableWorkflow): mixed}>
     */
    public static function resourceDependentCalls(): array
    {
        return [
            'getResource'         => [static fn(ConfigurableWorkflow $w): mixed => $w->getResource()],
            'getRouteId'          => [static fn(ConfigurableWorkflow $w): mixed => $w->getRouteId()],
            'getRoutePath'        => [static fn(ConfigurableWorkflow $w): mixed => $w->getRoutePath()],
            'getNavigationConfig' => [static fn(ConfigurableWorkflow $w): mixed => $w->getNavigationConfig()],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function slugs(): array
    {
        return [
            'single segment'       => ['about', '/about'],
            'nested'               => ['about/team', '/about/team'],
            'leading slash'        => ['/about', '/about'],
            'trailing slash'       => ['about/', '/about'],
            'doubled slashes'      => ['about//team', '/about/team'],
            'empty slug is home'   => ['', '/'],
            'zero segment is kept' => ['archive/0', '/archive/0'],
        ];
    }

    #[Test]
    #[DataProvider('slugs')]
    public function derivesTheRoutePathFromTheSlug(string $slug, string $expected): void
    {
        $workflow = new ConfigurableWorkflow();
        $workflow->setResource(new FakeResource($slug));

        static::assertSame($expected, $workflow->getRoutePath());
    }

    #[Test]
    public function describesTheNavigationPageWithWeeklyDefaults(): void
    {
        $workflow = new ConfigurableWorkflow();
        $workflow->setResource(new FakeResource());

        static::assertSame(
            [
                'label'      => 'Untitled',
                'route'      => 'page-1',
                'changefreq' => 'weekly',
                'priority'   => '0.5',
                'visible'    => true,
                'pages'      => [],
            ],
            $workflow->getNavigationConfig(),
        );
    }

    /**
     * @param callable(ConfigurableWorkflow): mixed $call
     */
    #[Test]
    #[DataProvider('resourceDependentCalls')]
    public function failsWhenNoResourceIsSet(callable $call): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource not set on workflow');

        $call(new ConfigurableWorkflow());
    }

    #[Test]
    #[DataProvider('missingMiddleware')]
    public function failsWithoutMiddleware(?string $middleware): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Middleware not configured for workflow');

        (new ConfigurableWorkflow(middleware: $middleware))->getRouteMiddleware();
    }

    #[Test]
    public function identifiesTheRouteByResourceTypeAndId(): void
    {
        $workflow = new ConfigurableWorkflow();
        $workflow->setResource(new FakeResource(
            id: 'abc',
            type: 'article',
        ));

        static::assertSame('article-abc', $workflow->getRouteId());
    }

    #[Test]
    #[DataProvider('labels')]
    public function labelsTheNavigationPageFromTheMostSpecificTitle(
        ?string $routeTitle,
        ?string $workflowTitle,
        string $expected,
    ): void {
        $workflow = new ConfigurableWorkflow(
            routeTitle: $routeTitle,
            workflowTitle: $workflowTitle,
        );
        $workflow->setResource(new FakeResource());

        static::assertSame($expected, $workflow->getNavigationConfig()['label']);
    }

    #[Test]
    public function prefersAConfiguredRoutePath(): void
    {
        $workflow = new ConfigurableWorkflow(routePath: '/fixed');

        static::assertSame('/fixed', $workflow->getRoutePath());
    }

    #[Test]
    public function returnsTheConfiguredMiddleware(): void
    {
        static::assertSame(
            'app.handler',
            (new ConfigurableWorkflow(middleware: 'app.handler'))->getRouteMiddleware(),
        );
    }

    #[Test]
    public function returnsTheResourceItWasGiven(): void
    {
        $resource = new FakeResource();
        $workflow = new ConfigurableWorkflow();
        $workflow->setResource($resource);

        static::assertSame($resource, $workflow->getResource());
    }
}
