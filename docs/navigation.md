# Navigation

`ResourceStrategy::getNavigationConfig()` returns one page per top-level
resource, with child pages nested under `pages`, in the shape used by
laminas-navigation and sitemap generators:

```php
[
    [
        'label'      => 'Untitled',
        'route'      => 'page-1',
        'visible'    => true,
        'lastmod'    => null,
        'changefreq' => 'monthly',
        'priority'   => '0.6',
        'pages'      => [
            ['label' => 'Untitled', 'route' => 'page-2', /* ... */ 'pages' => []],
        ],
    ],
]
```

Every resource gets a page, including resources without a route (for example
a folder with no middleware). Values the workflow's `getNavigationConfig()`
leaves out default to `visible: true`, `lastmod: null`, `changefreq: weekly`
and `priority: 0.5`.

Override `ResourceStrategy::getNavigationPage()` to change the page shape.
