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

## Alpha limitations

Service registration targets will be introduced by later work packages. Phase 3 supplies lifecycle infrastructure only and deliberately contains no placeholder container or service locator.
