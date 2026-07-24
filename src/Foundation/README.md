---
id: FOUNDATION-README
title: Runtime Foundation
summary: Runtime Foundation creates and orchestrates an isolated SIF application runtime. It contains no container, configuration loader, event dispatcher, module loader, HTTP dispatch, console routing, or database integration.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - runtime
  - foundation
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Runtime Foundation

Runtime Foundation creates and orchestrates an isolated SIF application runtime. It contains no container, configuration loader, event dispatcher, module loader, HTTP dispatch, console routing, or database integration.

## Architecture

`Framework` delegates graph construction to `Bootstrap`. Each `Application` owns its `Runtime`, `Kernel`, `Environment`, and `ServiceProviderCollection`. `Kernel` controls runtime transitions and delegates ordered hooks to `Lifecycle`.

## Public API

The compatibility-protected entry points are `Framework`, the Foundation contracts, `BootStage`, and `BootResult`. Provider extensions implement `ServiceProviderInterface` or extend `ServiceProvider`.

## Provider lifecycle

Providers are registered explicitly on an application:

```php
$application = \Sif\Foundation\Framework::create();
$application->providers()->add(new ApplicationProvider());

$bootResult = $application->run();
$shutdownResult = $application->shutdown();
```

Execution is deterministic:

1. `register()` for every provider in insertion order.
2. `boot()` for every provider in insertion order.
3. `shutdown()` for every provider in reverse insertion order.

Duplicate provider classes are rejected. A missing class lookup raises `ServiceProviderNotFoundException`.

## Error handling

A register or boot exception stops the current phase, marks the Runtime as failed, and returns a failed `BootResult` retaining the original cause. Shutdown attempts all providers in reverse order, records every failure as a typed `BootError`, marks the Runtime failed, and retains the first original cause through the singular `BootResult::cause()` API.

Providers must not mutate Runtime state, invoke Kernel lifecycle methods, use global state, or access optional modules. Provider priorities, dependencies, autodiscovery, and runtime events are not implemented.

## Capabilities and extension points

The component declares `runtime`, `foundation`, `providers`, and `lifecycle`. Provider instances are application-owned and are not required to be singletons.

Applications expose capabilities through `capabilities()`, `hasCapability()`, and `addCapability()`. Identifiers are trimmed, converted to ASCII lowercase, deduplicated in insertion order, and limited to letters, numbers, dots, hyphens, and underscores. Dots separate non-empty hierarchy segments.

## Observability preparation

The immutable event DTOs under `Sif\Foundation\Events` describe framework boot, application creation and lifecycle, kernel boot, shutdown, and failure. Constructing an event has no side effects and does not dispatch it. A future dispatcher may consume these objects without changing Runtime state or provider order.

Application event JSON contains only environment, runtime state and stage, capabilities, event name, and an ISO 8601 timestamp. It never serializes the Application or Runtime object. `FrameworkFailed` retains its original Throwable internally but serializes only a stable diagnostic code and throwable type; messages, traces, paths, and credentials are excluded.

## Alpha limitations

Service registration targets and event dispatch will be introduced by later work packages. Phase 4 supplies event data objects only and deliberately contains no placeholder dispatcher, listener registry, container, or service locator.
