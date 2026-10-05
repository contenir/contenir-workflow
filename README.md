# contenir/contenir-workflow-mezzio

Formerly `contenir/contenir-workflow`; the old package is abandoned in favour of this one.

[![Continuous Integration](https://github.com/contenir/contenir-workflow-mezzio/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/contenir/contenir-workflow-mezzio/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/contenir/contenir-workflow-mezzio/graph/badge.svg)](https://codecov.io/gh/contenir/contenir-workflow-mezzio)

Database-driven workflow system for [Mezzio](https://docs.mezzio.dev/) that
generates routes and navigation from a hierarchical page structure, for
[Contenir CMS](https://github.com/contenir).

Your application supplies a tree of resources (pages, articles, ...). For each
resource a *workflow* decides its route (path, middleware, defaults) and its
navigation page (label, sitemap change frequency and priority). The
`ResourceStrategy` walks the tree once, caches the result, and the routes are
registered with the Mezzio `Application` either when it is created (delegator
factory) or on the first request (middleware).

## Requirements

- PHP 8.3, 8.4 or 8.5
- mezzio/mezzio 3.18+
- laminas/laminas-servicemanager 3.22+
- laminas/laminas-cache 3.12+ with a storage adapter that can store arrays

The 0.x release remains available from the `0.x` branch and the `v0.1.0` tag;
see [UPGRADE-2.0.md](UPGRADE-2.0.md).

## Install

```bash
composer require contenir/contenir-workflow-mezzio
```

With [laminas-component-installer](https://docs.laminas.dev/laminas-component-installer/)
the `Contenir\Workflow\ConfigProvider` is added to your configuration
automatically. It registers `ResourceStrategy`, `WorkflowPluginManager` and
`WorkflowMiddleware`.

## Usage

### 1. Implement `ResourceInterface` on your entity

```php
use Contenir\Workflow\ResourceInterface;

final class Page implements ResourceInterface
{
    public function getSlug(): string { return $this->slug; }           // "about/team"
    public function getPrimaryKeys(): array { return ['page_id' => $this->id]; }
    public function getType(): string { return 'page'; }                 // route id prefix
    public function getId(): int|string { return $this->id; }
    public function getChildren(): iterable { return $this->children; } // array or Traversable

    /** Optional: PageWorkflow routes the page to this middleware service. */
    public function getMiddleware(): ?string { return $this->handler; }
}
```

### 2. Provide the resource tree with a `ResourceAdapterInterface`

```php
use Contenir\Workflow\Repository\ResourceAdapterInterface;

final class PageRepositoryAdapter implements ResourceAdapterInterface
{
    public function __construct(private PageRepository $pages) {}

    public function getWorkflowResources(): iterable
    {
        return $this->pages->findRootPages(); // top-level pages, children loaded
    }
}
```

### 3. Configure the workflow manager

```php
// config/autoload/workflow.global.php
use App\Repository\PageRepositoryAdapter;
use Contenir\Workflow\Factory\WorkflowApplicationDelegatorFactory;
use Contenir\Workflow\Strategy\ResourceStrategy;
use Mezzio\Application;

return [
    'workflow_manager' => [
        'strategy'   => ResourceStrategy::class,
        'repository' => PageRepositoryAdapter::class,
        'cache'      => 'FilesystemCache',
        'cache_key'  => 'WorkflowResourceCache',
    ],
    'dependencies' => [
        'delegators' => [
            Application::class => [WorkflowApplicationDelegatorFactory::class],
        ],
    ],
];
```

### 4. Handle the routed request

Each route carries the resource's primary keys as the `id` default, which the
router passes to the request as an attribute:

```php
final class PageHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = $request->getAttribute('id')['page_id'];
        // load and render the page
    }
}
```

## Public API

| Class | Purpose |
| --- | --- |
| `ResourceInterface` | A routable resource: slug, primary keys, type, id, children |
| `Repository\ResourceAdapterInterface` | Supplies the top-level resources |
| `Workflow\WorkflowInterface` | Turns one resource into a route config and a navigation config |
| `Workflow\AbstractWorkflow` | Base workflow: route id `<type>-<id>`, path from slug, navigation defaults |
| `Workflow\AbstractPageWorkflow` | Page route logic: routes a resource to its own `getMiddleware()` (or `$middleware`) |
| `Workflow\PageWorkflow` | The default page workflow (final): monthly, priority 0.6 |
| `Workflow\WorkflowPluginManager` | Plugin manager for workflows (`page`, `Page`, `PageWorkflow`) |
| `Workflow\WorkflowFactory` | Factory for workflows with a no-argument constructor |
| `Strategy\ResourceStrategyInterface` | `getRouteConfig()`, `getNavigationConfig()`, `clearCache()` |
| `Strategy\AbstractResourceStrategy` | Walks the tree and caches; extend it to customise (`getWorkflowType()`, `getNavigationPage()`) |
| `Strategy\ResourceStrategy` | The default strategy (final) |
| `Factory\WorkflowApplicationDelegatorFactory` | Registers the routes when the `Application` is created |
| `Middleware\WorkflowMiddleware` | Registers the routes on the first request instead |
| `ConfigProvider` and the `Factory\*` classes | Container wiring |

Every concrete class is `final`; the abstract classes and interfaces above
are the extension points.

The [docs](docs/) folder covers each area in detail:

- [Configuration](docs/configuration.md)
- [Resources and adapters](docs/resources.md)
- [Workflows](docs/workflows.md)
- [Route registration](docs/routes.md)
- [Navigation](docs/navigation.md)
- [Caching](docs/caching.md)

## Development

The QA toolchain is [php-db/phpdb-qa-tools](https://github.com/php-db/phpdb-qa-tools).
[Mago](https://mago.carthage.software/) is a standalone binary, installed
separately (`brew install mago`).

```bash
composer check             # everything below
composer cs-check          # mago format --check && mago lint
composer static-analysis   # mago analyze
composer test              # unit suite: collaborators doubled, no I/O
composer test-integration  # integration suite: real ServiceManager and Mezzio Application
composer test-coverage     # both suites, clover.xml for Codecov
composer mutation-test     # Infection mutation testing over both suites (needs Xdebug or PCOV)
```

## License

MIT. See [LICENSE](LICENSE).
