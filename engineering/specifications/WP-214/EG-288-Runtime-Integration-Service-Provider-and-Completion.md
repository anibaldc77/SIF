---
id: EG-288
title: Runtime Integration, Service Provider and Completion
summary: Integrates Error Handling and Recovery 2 with the SIF runtime through optional application contracts, a service provider, lifecycle failure observation and a compatibility-preserving bootstrap extension.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-28
updated: 2026-07-28
tags:
  - error-handling
  - runtime
  - lifecycle
  - service-provider
work_package: WP-214
depends_on:
  - EG-287
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-288 — Runtime Integration, Service Provider and Completion

## Objective

Integrate the provider-neutral error-handling subsystem with the runtime without changing the return type or operational semantics of `Application::boot()`, `run()` and `shutdown()`.

## Application contracts

`ErrorHandlingAwareApplicationInterface` exposes the configured handler and the last structured result. `MutableErrorHandlingApplicationInterface` adds provider-owned publication of the handler.

The handler is optional. Applications created without an `ErrorHandlingPlan` preserve the previous behavior and expose `null` for both accessors.

## Bootstrap integration

`Bootstrap` accepts an optional trailing `ErrorHandlingPlan`. When present it creates one `ErrorHandler`, registers `RuntimeErrorHandlingServiceProvider` and injects the same instance into `Application`.

The new constructor argument is trailing and nullable, preserving positional and named compatibility for existing callers.

## Runtime provider

`RuntimeErrorHandlingServiceProvider` publishes the handler during `register()` and contributes the `error-handling` capability. It performs no global exception registration and does not replace PHP error or shutdown handlers.

## Lifecycle failure observation

`Application` observes failed `BootResult` values after kernel execution. When a cause and handler are present, it creates one structured handling result with origin:

- `runtime.boot`
- `runtime.run`
- `runtime.shutdown`

Metadata includes the boot stage, error count and runtime state. The original `BootResult` and original throwable identity are preserved.

## Terminal observation boundary

Any throwable raised while observing a lifecycle failure is absorbed at a terminal boundary. The error subsystem must never replace, mask or recursively process the original runtime failure.

## Recovery boundary

Runtime integration records the `RecoveryDecision` but does not execute retries, delays, aborts, rethrows or degradation. Recovery execution remains owned by the caller or a future supervisor component.

## Compatibility

- No configured plan: previous runtime behavior.
- Existing boot/run/shutdown return types: unchanged.
- Logging and error handling: independently configurable.
- Existing provider order: preserved; the error-handling provider is appended when enabled.
