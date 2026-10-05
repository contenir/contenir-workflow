<?php

declare(strict_types=1);

namespace Contenir\Workflow\Tests\Unit\Strategy;

use Contenir\Workflow\ResourceInterface;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Contenir\Workflow\Tests\TestAsset\Repository\InMemoryResourceAdapter;
use Contenir\Workflow\Tests\TestAsset\Resource\FakeResource;
use Contenir\Workflow\Tests\TestAsset\Resource\ResourceFactory;
use Contenir\Workflow\Tests\TestAsset\Strategy\TypedResourceStrategy;
use Contenir\Workflow\Tests\TestAsset\Workflow\ConfigurableWorkflow;
use Contenir\Workflow\Tests\Trait\InMemoryCacheTrait;
use Contenir\Workflow\Workflow\PageWorkflow;
use Contenir\Workflow\Workflow\WorkflowInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\PluginManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_column;
use function array_keys;

#[Group('unit')]
final class ResourceStrategyTest extends TestCase
{
    use InMemoryCacheTrait;

    /**
     * @return array<string, array{iterable<ResourceInterface>}>
     */
    public static function childCollections(): array
    {
        $children = [
            ResourceFactory::page(2, 'about/team'),
            ResourceFactory::page(3, 'about/history'),
        ];

        return [
            'array'     => [$children],
            'generator' => [ResourceFactory::generate($children)],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableCacheEntries(): array
    {
        return [
            'string from a cache without serializer' => ['Array'],
            'array without navigation'               => [['route' => []]],
            'array without routes'                   => [['navigation' => []]],
        ];
    }

    #[Test]
    public function asksThePluginManagerForThePageWorkflow(): void
    {
        $pluginManager = $this->createMock(PluginManagerInterface::class);
        $pluginManager->expects($this->once())
            ->method('get')
            ->with('PageWorkflow')
            ->willReturn(new ConfigurableWorkflow());

        (new ResourceStrategy(
            new InMemoryResourceAdapter([new FakeResource()]),
            $pluginManager,
            $this->createInMemoryCache(),
        ))->getRouteConfig();
    }

    #[Test]
    public function buildsANavigationPageWithSitemapDefaults(): void
    {
        $strategy = $this->strategy([ResourceFactory::page(1, 'about')]);

        static::assertSame(
            [
                [
                    'label'      => 'Untitled',
                    'route'      => 'page-1',
                    'visible'    => true,
                    'lastmod'    => null,
                    'changefreq' => 'monthly',
                    'priority'   => '0.6',
                    'pages'      => [],
                ],
            ],
            $strategy->getNavigationConfig(),
        );
    }

    #[Test]
    public function buildsARouteForEveryResourceWithMiddleware(): void
    {
        $strategy = $this->strategy([
            ResourceFactory::page(1, 'about'),
            ResourceFactory::page(2, 'contact', 'contact.handler'),
        ]);

        static::assertSame(
            [
                'page-1' => [
                    'path'       => '/about',
                    'middleware' => 'page.handler',
                    'methods'    => ['GET'],
                    'name'       => 'page-1',
                    'options'    => ['defaults' => ['id' => ['page_id' => 1]]],
                ],
                'page-2' => [
                    'path'       => '/contact',
                    'middleware' => 'contact.handler',
                    'methods'    => ['GET'],
                    'name'       => 'page-2',
                    'options'    => ['defaults' => ['id' => ['page_id' => 2]]],
                ],
            ],
            $strategy->getRouteConfig(),
        );
    }

    #[Test]
    public function cachesTheBuiltRoutesAndNavigation(): void
    {
        $repository = new InMemoryResourceAdapter([ResourceFactory::page(1, 'about')]);
        $strategy   = $this->strategyFor($repository);

        $strategy->getRouteConfig();
        $strategy->getNavigationConfig();

        static::assertSame(1, $repository->calls);
        static::assertSame(['page-1'], array_keys($this->cacheItems['WorkflowResourceCache']['route']));
    }

    #[Test]
    public function clearsTheCachedBuild(): void
    {
        $strategy = $this->strategy([ResourceFactory::page(1, 'about')]);
        $strategy->getRouteConfig();

        $strategy->clearCache();

        static::assertSame([], $this->cacheItems);
    }

    #[Test]
    public function fillsInNavigationDefaultsMissingFromAWorkflow(): void
    {
        $workflow = $this->createStub(WorkflowInterface::class);
        $workflow->method('getRouteId')->willReturn('page-1');
        $workflow->method('getRouteConfig')->willReturn(null);
        $workflow->method('getNavigationConfig')->willReturn(['label' => 'About', 'route' => 'page-1']);

        $strategy = $this->strategy([new FakeResource()], $workflow);

        static::assertSame(
            [
                [
                    'label'      => 'About',
                    'route'      => 'page-1',
                    'visible'    => true,
                    'lastmod'    => null,
                    'changefreq' => 'weekly',
                    'priority'   => '0.5',
                    'pages'      => [],
                ],
            ],
            $strategy->getNavigationConfig(),
        );
    }

    #[Test]
    public function letsACustomStrategyChooseTheWorkflowPerResource(): void
    {
        $pluginManager = $this->createMock(PluginManagerInterface::class);
        $pluginManager->expects($this->once())
            ->method('get')
            ->with('article-workflow')
            ->willReturn(new ConfigurableWorkflow());

        (new TypedResourceStrategy(
            new InMemoryResourceAdapter([new FakeResource(type: 'article')]),
            $pluginManager,
            $this->createInMemoryCache(),
        ))->getRouteConfig();
    }

    /**
     * @param iterable<ResourceInterface> $children
     */
    #[Test]
    #[DataProvider('childCollections')]
    public function nestsChildPagesAndRoutesTheChildren(iterable $children): void
    {
        $strategy = $this->strategy([ResourceFactory::page(1, 'about', children: $children)]);

        $navigation = $strategy->getNavigationConfig();

        static::assertSame(['page-1', 'page-2', 'page-3'], array_keys($strategy->getRouteConfig()));
        static::assertSame(['page-2', 'page-3'], array_column($navigation[0]['pages'], 'route'));
    }

    #[Test]
    #[DataProvider('unusableCacheEntries')]
    public function rebuildsWhenTheCachedValueIsUnusable(mixed $cached): void
    {
        $this->cacheItems['WorkflowResourceCache'] = $cached;

        $routes = $this->strategy([ResourceFactory::page(1, 'about')])->getRouteConfig();

        static::assertSame(['page-1'], array_keys($routes));
    }

    #[Test]
    public function rejectsAPluginThatIsNotAWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow "PageWorkflow" must implement ' . WorkflowInterface::class);

        $this->strategy([new FakeResource()], new stdClass())->getRouteConfig();
    }

    #[Test]
    public function servesACachedBuildWithoutAskingTheRepository(): void
    {
        $this->cacheItems['WorkflowResourceCache'] = [
            'route'      => ['cached' => ['path' => '/cached']],
            'navigation' => [],
        ];
        $repository = new InMemoryResourceAdapter([ResourceFactory::page(1, 'about')]);

        $routes = $this->strategyFor($repository)->getRouteConfig();

        static::assertSame(['cached'], array_keys($routes));
        static::assertSame(0, $repository->calls);
    }

    #[Test]
    public function skipsTheRouteButKeepsTheNavigationPageForAResourceWithoutMiddleware(): void
    {
        $strategy = $this->strategy([new FakeResource('folder', 7)]);

        static::assertSame([], $strategy->getRouteConfig());
        static::assertSame('page-7', $strategy->getNavigationConfig()[0]['route']);
    }

    #[Test]
    public function usesTheConfiguredCacheKey(): void
    {
        $strategy = new ResourceStrategy(
            new InMemoryResourceAdapter([ResourceFactory::page(1, 'about')]),
            $this->pluginManager(new PageWorkflow()),
            $this->createInMemoryCache(),
            ['cache_key' => 'SiteRoutes'],
        );

        $strategy->getRouteConfig();

        static::assertSame(['SiteRoutes'], array_keys($this->cacheItems));
    }

    private function pluginManager(object $workflow): PluginManagerInterface
    {
        $pluginManager = $this->createStub(PluginManagerInterface::class);
        $pluginManager->method('get')->willReturn($workflow);

        return $pluginManager;
    }

    /**
     * @param list<ResourceInterface> $resources
     */
    private function strategy(array $resources, ?object $workflow = null): ResourceStrategy
    {
        return new ResourceStrategy(
            new InMemoryResourceAdapter($resources),
            $this->pluginManager($workflow ?? new PageWorkflow()),
            $this->createInMemoryCache(),
        );
    }

    private function strategyFor(InMemoryResourceAdapter $repository): ResourceStrategy
    {
        return new ResourceStrategy(
            $repository,
            $this->pluginManager(new PageWorkflow()),
            $this->createInMemoryCache(),
        );
    }
}
