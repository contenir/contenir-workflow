# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [2.0.0] - Unreleased

The public API keeps its shape. The major version marks the move to PHP 8.3+,
the php-db QA toolchain shared by all Contenir 2.x packages, and a few
breaking changes listed in [UPGRADE-2.0.md](UPGRADE-2.0.md).

### Changed

- Requires PHP 8.3, 8.4 or 8.5, mezzio/mezzio 3.18+,
  laminas/laminas-servicemanager 3.22+ and laminas/laminas-cache 3.12+.
- `ConfigProvider`, the `Factory\*` classes, `WorkflowMiddleware` and
  `WorkflowFactory` are `final`.
- `WorkflowPluginManager` is built by the new
  `Factory\WorkflowPluginManagerFactory` instead of a closure, so the merged
  configuration can be cached.
- `PageWorkflow` only routes to middleware that is a non-empty string.
- Factories, the delegator and `ResourceStrategy` check the types of the
  services they fetch and throw `InvalidArgumentException` with the service
  name, instead of failing later with a `TypeError`.
- `WorkflowFactory` throws `InvalidArgumentException` for an unknown class or
  one that does not implement `WorkflowInterface`.

### Fixed

- Registered routes now carry their `options`, so the resource's primary keys
  reach the request as the `id` attribute. They were dropped before, and
  `$request->getAttribute('id')` was always `null`.
- Resources whose `getChildren()` returns a non-countable `Traversable` (for
  example a generator) no longer fail with a `TypeError` from `count()`.
- A cached value that is not a routes-and-navigation array (such as the
  string `"Array"` from a Filesystem cache without the Serializer plugin) is
  rebuilt instead of failing with a `TypeError`.
- A slug segment of `0` is kept in the route path (`archive/0` was routed as
  `/archive`).
- The README now names the right package and explains that the delegator
  factory must be registered by the application.

### Added

- Continuous integration on PHP 8.3, 8.4 and 8.5 against lowest, locked and
  latest dependencies, with coverage reported to Codecov.
- Unit (no I/O) and integration (real ServiceManager and Mezzio Application)
  test suites, with 100% line and branch coverage.
- `docs/` pages for configuration, resources, workflows, route registration,
  navigation and caching.

### Removed

- `phpstan/phpstan`, `laminas/laminas-coding-standard`, `phpcs.xml`,
  `phpstan.neon` and `phpunit.xml`, replaced by Mago via
  `php-db/phpdb-qa-tools` and `phpunit.xml.dist`.

## [0.1.0]

- Initial release: resource strategy, page workflow, workflow plugin
  manager, route-registering delegator factory and middleware, with code
  quality tooling (phpcs, PHPStan).
