# Upgrading from 0.x to 2.0

2.0 keeps the 0.1 API shape. Most applications only need to update the
constraint and check the points below.

| | 0.x | 2.0 |
| --- | --- | --- |
| PHP | ^8.3 | 8.3, 8.4 or 8.5 |
| mezzio/mezzio | ^3.18 | ^3.18 |
| laminas/laminas-servicemanager | ^3.0 | ^3.22 |
| laminas/laminas-cache | ^3.0 | ^3.12 |
| laminas/laminas-cache-storage-adapter-filesystem | ^2.0 | ^2.4 |
| laminas/laminas-diactoros | ^3.0 | ^3.3 |

```bash
composer require contenir/contenir-workflow:^2.0
```

Projects that must stay on 0.x can keep using `^0.1`, maintained on the
`0.x` branch.

## Final classes

These classes can no longer be extended: `ConfigProvider`,
`Factory\ResourceStrategyFactory`, `Factory\WorkflowApplicationDelegatorFactory`,
`Factory\WorkflowMiddlewareFactory`, `Middleware\WorkflowMiddleware` and
`Workflow\WorkflowFactory`. Decorate or replace them instead.

```php
// 0.x
class MyMiddlewareFactory extends WorkflowMiddlewareFactory { /* ... */ }

// 2.0
final class MyMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): WorkflowMiddleware
    {
        $middleware = (new WorkflowMiddlewareFactory())($container);
        // ...
        return $middleware;
    }
}
```

`ResourceStrategy`, `AbstractWorkflow`, `PageWorkflow` and
`WorkflowPluginManager` remain extendable.

## Route options are applied

Routes are now registered with their `options`. Each route's
`defaults.id` holds the resource's primary keys, so a matched request has an
`id` attribute:

```php
// 0.x: always null, the options were dropped
$request->getAttribute('id');

// 2.0
$request->getAttribute('id'); // ['page_id' => 12]
```

If you worked around this by looking the resource up another way, that code
keeps working; the attribute is simply available now.

## PageWorkflow middleware must be a string

A resource's `getMiddleware()` (or the workflow's `$middleware`) must be a
non-empty string service name to produce a route.

```php
// 0.x: any truthy value was used as the middleware
public function getMiddleware(): array { return [AuthMiddleware::class, PageHandler::class]; }

// 2.0: name one service; build pipelines in that service's factory
public function getMiddleware(): string { return PageHandler::class; }
```

## Service type checks

Services fetched by the factories, the delegator and the strategy are type
checked. A misconfigured service now throws `InvalidArgumentException`
naming it, where 0.x failed with a `TypeError`:

| Service | Must be |
| --- | --- |
| `workflow_manager.strategy` | `Strategy\ResourceStrategy` |
| `workflow_manager.repository` | `Repository\ResourceAdapterInterface` |
| `workflow_manager.cache` | `Laminas\Cache\Storage\StorageInterface` |
| plugin returned for a resource | `Workflow\WorkflowInterface` |

`WorkflowFactory` throws `InvalidArgumentException` for a class that does not
exist or does not implement `WorkflowInterface`.

## Plugin manager factory

`WorkflowPluginManager` is now built by `Factory\WorkflowPluginManagerFactory`.
If you overrode the closure registered in 0.x, register your own factory
class under `WorkflowPluginManager::class` instead.

## Route paths keep `0` segments

```php
// slug "archive/0"
// 0.x: /archive
// 2.0: /archive/0
```
