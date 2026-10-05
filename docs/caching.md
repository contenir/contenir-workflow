# Caching

The strategy builds routes and navigation once and stores both in the
configured laminas-cache storage under `cache_key`
(`WorkflowResourceCache` by default). Later calls, including later requests,
read the cached copy and do not call the repository.

## The storage must hold arrays

The cached value is a PHP array. Storage adapters that only hold strings,
such as the Filesystem adapter, need the Serializer plugin:

```php
'caches' => [
    'FilesystemCache' => [
        'adapter' => Laminas\Cache\Storage\Adapter\Filesystem::class,
        'options' => ['cache_dir' => 'data/cache'],
        'plugins' => [
            ['name' => Laminas\Cache\Storage\Plugin\Serializer::class],
        ],
    ],
],
```

If the cached value is not a routes-and-navigation array (for example the
string `"Array"` written by a Filesystem adapter without the serializer), the
strategy rebuilds from the repository instead of failing.

## Clearing

Call `ResourceStrategy::clearCache()` after pages are added, moved or
renamed. Routes already registered with the running `Application` are not
removed; the next process picks up the new tree.
