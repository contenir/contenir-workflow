# Configuration

All settings live under the `workflow_manager` key of the application config.

| Key | Required | Default | Used by |
| --- | --- | --- | --- |
| `strategy` | for route registration | none | `WorkflowApplicationDelegatorFactory`, `WorkflowMiddlewareFactory` |
| `repository` | yes | none | `ResourceStrategyFactory` |
| `cache` | no | `FilesystemCache` | `ResourceStrategyFactory` |
| `cache_key` | no | `WorkflowResourceCache` | `ResourceStrategyFactory` |
| `use_parent_as_landing_page` | no | `false` | Stored in the strategy options; not yet acted on |

- `strategy` is a service name that must resolve to a
  `ResourceStrategyInterface` (normally `ResourceStrategy::class` itself, or
  your `AbstractResourceStrategy` subclass).
- `repository` is a service name that must resolve to a
  `Repository\ResourceAdapterInterface`.
- `cache` is a service name that must resolve to a
  `Laminas\Cache\Storage\StorageInterface`. See [caching](caching.md).

Invalid values fail when the service is built, with an
`InvalidArgumentException` naming the problem: `No repository configured in
workflow_manager`, `No strategy configured in workflow_manager`, `Repository
must implement ...`, `Cache must implement StorageInterface`, or `Service "X"
must be a ...`.

## Services

`ConfigProvider` registers these factories:

| Service | Factory |
| --- | --- |
| `Strategy\ResourceStrategy` | `Factory\ResourceStrategyFactory` |
| `Workflow\WorkflowPluginManager` | `Factory\WorkflowPluginManagerFactory` |
| `Middleware\WorkflowMiddleware` | `Factory\WorkflowMiddlewareFactory` |

The `WorkflowApplicationDelegatorFactory` is **not** registered for you,
because it and `WorkflowMiddleware` are alternatives. See
[route registration](routes.md).

The provider holds only class names, so the merged configuration can be
cached by laminas-config-aggregator.
