# Contenir Workflow

Database-driven workflow system for Mezzio that automatically generates routes and navigation from hierarchical page structures.

## Features

- **Dynamic Route Generation** - Routes are automatically created from database page hierarchy
- **Cached Performance** - Route and navigation structures are cached to avoid repeated database queries
- **Workflow-Based Extensibility** - Different content types can use different workflow implementations
- **Integrated Navigation** - Navigation menus with SEO metadata (sitemap-ready)
- **Strategy Pattern** - Flexible resource processing through configurable strategies

## Installation

```bash
composer require contenir/workflow
```

## Quick Start

### 1. Configure the Workflow Manager

Add to your `config/autoload/workflow.global.php`:

```php
<?php

use Contenir\Workflow\Strategy\ResourceStrategy;
use App\Repository\PageRepositoryAdapter;

return [
    'workflow_manager' => [
        'strategy' => ResourceStrategy::class,
        'repository' => PageRepositoryAdapter::class,
        'cache' => 'FilesystemCache',
        'cache_key' => 'WorkflowResourceCache',
    ],
];
```

### 2. Create a Resource Adapter

```php
<?php

namespace App\Repository;

use Contenir\Workflow\Repository\ResourceAdapterInterface;

class PageRepositoryAdapter implements ResourceAdapterInterface
{
    public function __construct(private PageRepository $repository)
    {
    }

    public function getWorkflowResources(): iterable
    {
        // Return top-level pages with children
        return $this->repository->findRootPages();
    }
}
```

### 3. Implement ResourceInterface on Your Entity

```php
<?php

namespace App\Entity;

use Contenir\Workflow\ResourceInterface;

class Page implements ResourceInterface
{
    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPrimaryKeys(): array
    {
        return ['page_id' => $this->id];
    }

    public function getType(): string
    {
        return 'page';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }
}
```

### 4. Create a Handler for Pages

```php
<?php

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = $request->getAttribute('id')['page_id'];
        // Load and render page...
    }
}
```

## How It Works

1. **Bootstrap** - The `WorkflowApplicationDelegatorFactory` intercepts Application creation
2. **Strategy** - Gets routes from `ResourceStrategy` which queries the database via your adapter
3. **Caching** - Routes and navigation are cached on first request
4. **Registration** - Routes are registered with Mezzio's router
5. **Request** - Incoming requests match against generated routes and dispatch to configured middleware

## License

MIT
