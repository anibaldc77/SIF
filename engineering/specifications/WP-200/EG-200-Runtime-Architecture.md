---
id: EG-200
title: SIF Runtime Architecture
summary: Defines the responsibility boundaries, lifecycle, state model, contracts, and capability-driven extension model of SIF Runtime 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - kernel
  - lifecycle
  - capabilities
  - architecture
work_package: WP-200
depends_on:
  - WP-003-RUNTIME-FOUNDATION
related_adrs:
  - ADR-0004
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-200 — SIF Runtime Architecture

## 1. Purpose

This specification defines the architectural baseline for SIF Runtime 2.0. It governs every subsequent Runtime Work Package and establishes the stable boundaries between the application, Runtime, Kernel, container, modules, providers, capabilities, and execution context.

## 2. Architectural objective

SIF Runtime is the framework lifecycle authority. It coordinates startup, operation, and shutdown without owning business behavior and without depending on optional infrastructure implementations.

```text
Application
    -> Runtime
        -> Kernel
            -> Lifecycle
            -> Capability Registry
            -> Container
            -> Modules and Service Providers
            -> Runtime Context
```

## 3. Scope

EG-200 defines:

- Runtime and Kernel responsibilities;
- lifecycle stages and legal transitions;
- minimum public contracts;
- the capability-driven service model;
- relationships with the dependency injection container;
- module and provider integration boundaries;
- Runtime Context ownership;
- error, shutdown, and observability principles;
- compatibility and extension rules.

It does not implement the container, registry, configuration system, module discovery, event dispatcher, or application adapters.

## 4. Principles

1. **Single lifecycle authority:** only the Kernel advances Runtime state.
2. **Contract-first design:** Core depends on interfaces and immutable value objects.
3. **Capability-driven access:** replaceable framework services are accessed through governed capabilities.
4. **Container containment:** the container constructs objects; it is not the framework's public facade.
5. **Determinism:** registration, startup, resolution, and shutdown order are reproducible.
6. **Fail-fast startup:** invalid mandatory configuration or missing required capabilities stops startup.
7. **Best-effort shutdown:** all eligible shutdown operations are attempted and failures are accumulated.
8. **No business knowledge:** Runtime contains no domain or application-specific rules.
9. **Observability:** every lifecycle transition and failure can be reported with context.
10. **Backward compatibility:** public contracts and capability identifiers follow SemVer.

## 5. Core responsibilities

### 5.1 Runtime

The Runtime is the application-facing execution boundary. It exposes state and governed operations, delegates lifecycle transitions to the Kernel, and provides controlled access to capabilities and context.

The Runtime SHALL NOT:

- instantiate arbitrary application services directly;
- discover modules by undocumented conventions;
- expose the complete mutable container;
- silently continue after a failed mandatory boot stage.

### 5.2 Kernel

The Kernel is the lifecycle orchestrator. It validates preconditions, invokes boot stages, coordinates providers and modules, commits state transitions, and performs shutdown in reverse dependency order.

### 5.3 Lifecycle

The lifecycle defines the ordered stages and their results. Existing WP-003 concepts (`BootStage`, `BootResult`, `RuntimeState`, `BootError`, and `BootWarning`) SHALL be reused or evolved compatibly rather than duplicated.

### 5.4 Capability Registry

The registry maps governed capability identifiers to provider descriptors and resolved implementations. Its detailed behavior belongs to WP-202.

### 5.5 Container

The container resolves object graphs and lifetimes. Capability resolution may delegate construction to the container, but capability policy remains outside it.

### 5.6 Runtime Context

The Runtime Context carries execution-scoped information such as environment, execution identifiers, locale, clock, correlation data, and—when an application adapter supplies them—request and authenticated principal data.

## 6. Runtime state model

The normative state sequence is:

```text
created
  -> preparing
  -> registering
  -> booting
  -> ready
  -> running
  -> stopping
  -> stopped
```

Failure states:

```text
preparing/registering/booting -> failed
running/stopping              -> failed
failed                        -> stopping -> stopped
```

Rules:

- transitions are one-way except where an explicit restart feature is specified in a future release;
- `ready` means mandatory providers and capabilities are available;
- `running` means the application execution callback or adapter has started;
- shutdown is idempotent from `stopping` or `stopped`;
- a failed startup may still require shutdown of already-booted components.

## 7. Lifecycle stages

1. **Prepare:** load environment and immutable bootstrap inputs.
2. **Register:** register providers, module descriptors, container bindings, and capability declarations.
3. **Validate:** verify dependencies, required capabilities, ordering, configuration, and cycles.
4. **Boot:** initialize providers and modules in deterministic order.
5. **Ready:** freeze startup registries where required and publish readiness.
6. **Run:** delegate execution to an application adapter.
7. **Shutdown:** stop modules/providers in reverse order and release resources.
8. **Finalize:** emit the final report and terminal state.

A stage SHALL return a structured result. Exceptions may be captured as causes but SHALL NOT replace structured diagnostics.

## 8. Capability model baseline

### 8.1 Identity

A capability identifier is a stable lower-case dotted name, for example:

```text
runtime.logger
runtime.clock
config.repository
events.dispatcher
cache.store
```

Identifiers beginning with `sif.` or `runtime.` are reserved for official components. Exact naming rules will be finalized by WP-202.

### 8.2 Requirement levels

- **required:** Runtime cannot become ready without it;
- **optional:** absence is valid and queryable;
- **multiple:** zero or more ordered providers may coexist.

### 8.3 Provider descriptor

A provider declaration minimally contains:

- capability identifier;
- implementation service identifier or factory reference;
- provider identifier;
- priority;
- lifecycle/lifetime classification;
- replacement and decoration metadata;
- source module;
- compatibility version.

### 8.4 Resolution

Resolution SHALL be deterministic and SHALL reject unresolved ambiguity. Priority alone must not silently select between providers when policy requires explicit replacement or default designation.

### 8.5 Boundaries

Capabilities are for stable framework-level services. Domain services and arbitrary application objects SHALL remain normal container services and SHALL NOT be converted into capabilities merely for convenience.

## 9. Module and provider relationship

A module describes a bounded framework extension. A Service Provider performs registrations and lifecycle work for that module.

A provider may publish:

- container services;
- capabilities;
- configuration namespaces;
- commands;
- resources;
- routes through an optional routing capability;
- translations and assets through their respective capabilities.

Publication SHALL occur during registration; use of required capabilities SHALL occur only after validation guarantees their availability.

## 10. Public contract baseline

The following conceptual contracts are required, with exact PHP signatures deferred to implementation specifications:

- `RuntimeInterface`;
- `KernelInterface`;
- `LifecycleInterface`;
- `RuntimeStateInterface` or immutable state value object;
- `CapabilityRegistryInterface`;
- `CapabilityResolverInterface`;
- `CapabilityProviderInterface` or descriptor contract;
- `RuntimeContextInterface`;
- `ApplicationAdapterInterface`;
- existing provider and application contracts from WP-003 where compatible.

No implementation Work Package may introduce a competing contract without an ADR.

## 11. Failure model

Failures SHALL identify:

- lifecycle stage;
- stable diagnostic code;
- human-readable message;
- component/provider/module source;
- execution or correlation identifier when available;
- original throwable cause when applicable;
- recoverability and severity.

Startup failures stop forward progress. Shutdown failures are aggregated while shutdown continues.

## 12. Concurrency and isolation

Runtime 2.0-alpha1 assumes one lifecycle transition at a time. Contracts SHALL avoid global mutable state so future long-running workers, concurrent requests, and isolated Runtime instances remain possible.

## 13. Security constraints

- secrets must not be serialized into reports or generated artifacts;
- capability replacement must be explicit and observable;
- untrusted modules must not gain unrestricted container mutation after readiness;
- Runtime Context must distinguish authenticated principal data from generic metadata;
- diagnostic output must support redaction.

## 14. Compatibility

Public Runtime interfaces, lifecycle state names, diagnostic codes, artifact schemas, and official capability identifiers are public compatibility surfaces once marked stable. Experimental contracts must be explicitly labeled and excluded from compatibility guarantees.

## 15. Work Package decomposition

- **WP-200:** Runtime architecture baseline.
- **WP-201:** Dependency Injection boundaries and Runtime integration.
- **WP-202:** Capability Registry and resolution model.
- **WP-203:** Configuration system and configuration capability.
- **WP-204:** Module system and provider publication pipeline.
- **WP-205:** Runtime Context and execution identity.
- **WP-206:** Runtime events and dispatcher capability.

## 16. Acceptance criteria

EG-200 is accepted when:

- Runtime and Kernel responsibilities are unambiguous;
- lifecycle stages and failure semantics are defined;
- ADR-0005 is approved;
- capability boundaries prevent the registry from becoming a generic service locator;
- WP-003 compatibility requirements are identified;
- downstream WP-201 through WP-206 can derive their contracts without changing this architecture;
- repository metadata and references validate successfully.
