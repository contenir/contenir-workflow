# Workflows

A workflow turns one resource into a route and a navigation page. The
strategy asks the `WorkflowPluginManager` for a workflow by name, calls
`setResource()`, then reads `getRouteConfig()`, `getRouteId()` and
`getNavigationConfig()`.

## WorkflowInterface

| Method | Returns |
| --- | --- |
| `setResource(ResourceInterface)` | `void` |
| `getResource()` | the resource |
| `getRouteId()` | unique route name |
| `getRoutePath()` | URL path |
| `getRouteMiddleware()` | middleware service name |
| `getRouteConfig()` | `array{path, middleware, methods, name, options?}` or `null` for no route |
| `getNavigationConfig()` | `array{label, route, visible?, lastmod?, changefreq?, priority?, pages?}` |

## AbstractWorkflow

Implements everything except `getRouteConfig()`:

- **Route id:** `<type>-<id>`, e.g. `page-12`.
- **Route path:** `$routePath` when set, otherwise the slug with empty
  segments removed and a single leading slash: `about//team/` becomes
  `/about/team`, an empty slug becomes `/`.
- **Middleware:** `$middleware`; `getRouteMiddleware()` throws a
  `RuntimeException` when it is not set.
- **Navigation:** label from `$routeTitle`, then `$workflowTitle`, then
  `Untitled`; `changefreq` and `priority` from `$changeFrequency` (`weekly`)
  and `$priority` (`0.5`).

Calling a method that needs the resource before `setResource()` throws
`RuntimeException('Resource not set on workflow')`.

## PageWorkflow

Routes the resource to the middleware named by the resource's own
`getMiddleware()` method, or the workflow's `$middleware` property when the
resource has no such method. When that is not a non-empty string the resource
gets no route, only a navigation page. Pages use `monthly` and priority `0.6`.

```php
[
    'path'       => '/about/team',
    'middleware' => App\Handler\PageHandler::class,
    'methods'    => ['GET'],
    'name'       => 'page-12',
    'options'    => ['defaults' => ['id' => ['page_id' => 12]]],
]
```

## Custom workflows

Extend `AbstractWorkflow` (or `PageWorkflow`), register it with the plugin
manager, and pick it per resource by overriding
`ResourceStrategy::getWorkflowType()`:

```php
final class ArticleWorkflow extends PageWorkflow
{
    protected string $changeFrequency = 'daily';
}

final class SiteStrategy extends ResourceStrategy
{
    protected function getWorkflowType(ResourceInterface $resource): string
    {
        return $resource->getType() === 'article' ? ArticleWorkflow::class : 'PageWorkflow';
    }
}
```

`WorkflowFactory` builds any workflow with a no-argument constructor, and
throws `InvalidArgumentException` for an unknown class or one that does not
implement `WorkflowInterface`. The plugin manager returns shared instances,
so a workflow must not keep state beyond its current resource.
