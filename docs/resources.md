# Resources and adapters

## ResourceInterface

A resource is anything that should get a route and a navigation page.

| Method | Returns | Used for |
| --- | --- | --- |
| `getSlug()` | `string` | The route path, e.g. `about/team` becomes `/about/team` |
| `getPrimaryKeys()` | `array<string, mixed>` | The route's `id` default |
| `getType()` | `string` | First half of the route id, `<type>-<id>` |
| `getId()` | `int\|string` | Second half of the route id |
| `getChildren()` | `iterable<ResourceInterface>` | Nested routes and navigation pages |

`getChildren()` may return an array or any `Traversable`, including a
generator.

A resource may also have a `getMiddleware()` method. It is not part of the
interface; `PageWorkflow` calls it when it exists. See [workflows](workflows.md).

## ResourceAdapterInterface

```php
interface ResourceAdapterInterface
{
    /** @return iterable<ResourceInterface> */
    public function getWorkflowResources(): iterable;
}
```

Return the top-level resources with their children available. The strategy
calls it only when the cache is empty.
