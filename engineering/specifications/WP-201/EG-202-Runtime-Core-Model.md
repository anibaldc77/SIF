---
id: EG-202
title: SIF Runtime Core Model
summary: Defines the normative core model, responsibilities, states, invariants, contracts, lifecycle flows, failure semantics, and compatibility rules for SIF Runtime Core.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - kernel
  - lifecycle
  - state-machine
  - contracts
  - architecture
work_package: WP-201
depends_on:
  - WP-003-RUNTIME-FOUNDATION
  - EG-200
  - EG-201
related_adrs:
  - ADR-0004
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-202 — SIF Runtime Core Model

## 1. Purpose

This specification defines the normative model of the SIF Runtime Core. It refines the Runtime Foundation delivered by WP-003 without replacing its public contracts prematurely and establishes the precise behavior that later WP-201 implementation increments SHALL follow.

The Runtime Core is the smallest framework subsystem capable of creating an application execution boundary, coordinating its lifecycle, exposing its current state, and producing deterministic results for startup, execution, failure, and shutdown.

## 2. Scope

EG-202 governs:

- the conceptual roles of `Application`, `Runtime`, `Kernel`, `Lifecycle`, `Bootstrap`, `Environment`, and `RuntimeContext`;
- ownership of lifecycle transitions;
- Runtime states, boot stages, transitions, and invariants;
- public command and observation contracts;
- startup, run, shutdown, and failure flows;
- error accumulation and causal failure preservation;
- time and identity boundaries required for deterministic testing;
- compatibility with WP-003;
- extension boundaries for capabilities, modules, configuration, events, and adapters.

EG-202 does not implement:

- the Capability Registry;
- dependency injection internals;
- module discovery;
- configuration loading;
- an event dispatcher;
- HTTP or console adapters;
- persistence, logging, cache, queue, mail, audit, or domain services.

## 3. Core principle

The Runtime Core SHALL remain minimal. A component belongs to Runtime Core only when it satisfies all of the following:

1. it is indispensable to establish or control application execution;
2. it does not depend on external infrastructure;
3. it contains no business rules;
4. it can be tested in isolation;
5. it is reusable by every supported application adapter.

Components that fail any condition SHALL be implemented as capabilities, modules, adapters, or application services.

## 4. Conceptual model

```text
Framework facade
    -> Application
        -> Kernel                 lifecycle authority
            -> Lifecycle          ordered orchestration
                -> Bootstrap      preparation policy
                -> Providers      extension participation
        -> Runtime                observable execution state
        -> Environment            immutable deployment identity
        -> Runtime Context        governed execution metadata
```

Future service resolution remains outside this core model:

```text
Runtime
    -> Capability Registry        WP-202
        -> Container              WP-201/WP-202 integration boundary
            -> Provider implementation
```

## 5. Responsibility model

### 5.1 Application

`Application` is the composition root owned by the framework consumer. It SHALL aggregate the Runtime Core collaborators required for one application instance.

It SHALL:

- expose the associated `Runtime`;
- expose the `Kernel` used to control the lifecycle;
- expose the ordered provider collection;
- expose immutable environment information;
- expose the Runtime Context when that context is available;
- delegate lifecycle commands to the Kernel.

It SHALL NOT:

- mutate Runtime state directly;
- implement provider ordering rules;
- resolve arbitrary services;
- contain adapter-specific request handling.

### 5.2 Runtime

`Runtime` is the authoritative observable record of the current execution state.

It SHALL expose:

- current Runtime state;
- current boot stage;
- primary failure cause, when present;
- start and stop timestamps, when present;
- state predicates defined by its public contract.

It SHALL NOT independently decide the next lifecycle state. State mutation is an internal mechanism invoked only by the Kernel or a Kernel-owned transition authority.

### 5.3 Kernel

`Kernel` is the sole lifecycle authority.

It SHALL:

- accept lifecycle commands;
- validate command preconditions;
- authorize legal transitions;
- delegate ordered work to `Lifecycle`;
- translate thrown failures into `BootResult` values;
- preserve the first causal `Throwable`;
- guarantee deterministic shutdown semantics.

No other public component SHALL advance Runtime state.

### 5.4 Lifecycle

`Lifecycle` executes ordered boot and shutdown participation.

It SHALL:

- preserve provider insertion order during registration and boot;
- use reverse order during shutdown;
- stop startup on a register or boot failure;
- continue eligible shutdown operations after individual failures;
- return accumulated structured diagnostics through `BootResult`.

It SHALL NOT own Runtime state transitions.

### 5.5 Bootstrap

`Bootstrap` represents preparation operations required before providers are booted. Its behavior SHALL remain deterministic, bounded, and independent of optional infrastructure.

### 5.6 Environment

`Environment` is immutable deployment identity. At minimum it distinguishes the named environment and debug posture established during application composition.

Environment values SHALL NOT be read through global mutable state after construction.

### 5.7 Runtime Context

`RuntimeContext` is the governed execution metadata boundary. In WP-201 it is a minimal contract and immutable value model only. Population from HTTP, console, queue, scheduler, or worker adapters belongs to later Work Packages.

The initial model SHOULD support stable optional values for:

- execution identifier;
- correlation identifier;
- trace identifier;
- locale;
- actor or principal reference;
- adapter name;
- start instant.

Runtime Context SHALL NOT expose the mutable dependency injection container.

## 6. State model

### 6.1 Compatibility baseline

WP-003 introduced these public states:

```text
Created
Bootstrapping
Booted
Running
Stopping
Stopped
Failed
```

WP-201 SHALL preserve these enum cases and their public meaning during SIF `2.0.0-alpha1` unless an independently approved compatibility migration explicitly changes them.

### 6.2 Legal transitions

```text
Created       -> Bootstrapping | Failed
Bootstrapping -> Booted        | Failed
Booted        -> Running       | Stopping | Failed
Running       -> Stopping      | Failed
Stopping      -> Stopped       | Failed
Stopped       -> terminal
Failed        -> terminal
```

The `Booted -> Stopping` transition SHALL be supported so an application that completed boot but was never entered into the running adapter loop can still shut down cleanly.

A transition not listed above SHALL fail with `InvalidRuntimeTransitionException` and SHALL NOT partially mutate Runtime state.

### 6.3 State invariants

1. A newly constructed Runtime is `Created` at `BootStage::Created`.
2. `startedAt` is assigned exactly once when leaving `Created` for `Bootstrapping`.
3. `stoppedAt` is assigned exactly once on terminal transition to `Stopped` or `Failed`.
4. A successful `BootResult` cannot contain errors or a causal failure.
5. A failed `BootResult` contains at least one `BootError`.
6. The first causal failure is preserved even when shutdown produces additional failures.
7. Terminal states reject all further state changes.
8. Runtime state and boot stage are updated atomically from the caller's perspective.
9. A Runtime instance belongs to exactly one Application instance.
10. A Kernel SHALL NOT operate on an Application whose Runtime is already terminal.

## 7. Boot stages

Boot stages describe the currently executing lifecycle activity and SHALL remain distinct from Runtime state.

The WP-003 `BootStage` public cases SHALL remain compatibility-protected. WP-201 MAY add stages only when:

- the addition is backward compatible;
- ordering is explicitly specified;
- all serialization and reporting behavior is defined;
- Builder documentation and tests are updated.

A stage is diagnostic context; it is not an alternative state machine.

## 8. Command model

The Kernel exposes three lifecycle commands:

```php
boot(ApplicationInterface $application): BootResult
run(ApplicationInterface $application): BootResult
shutdown(ApplicationInterface $application): BootResult
```

### 8.1 `boot`

Precondition: Runtime is `Created`.

Postconditions:

- success: Runtime is `Booted`;
- failure: Runtime is `Failed`;
- all provider registration and boot ordering guarantees have been respected.

Calling `boot` in any other state SHALL return or throw a deterministic lifecycle error according to the final WP-201 contract decision; it SHALL never silently succeed.

### 8.2 `run`

`run` is an adapter-neutral transition into active execution. It does not implement an HTTP loop, console loop, queue worker, or scheduler.

Permitted behavior:

- when Runtime is `Created`, Kernel MAY perform `boot` first and then transition to `Running`;
- when Runtime is `Booted`, Kernel transitions directly to `Running`;
- in any other state, invocation fails deterministically.

Success postcondition: Runtime is `Running`.

### 8.3 `shutdown`

Permitted source states:

- `Booted`;
- `Running`.

Shutdown SHALL:

1. transition to `Stopping`;
2. invoke provider shutdown in reverse boot order;
3. continue after individual shutdown failures;
4. aggregate all shutdown errors;
5. transition to `Stopped` when no terminal failure policy requires `Failed`;
6. preserve the first causal failure in the result.

Calling shutdown from `Created`, `Stopping`, `Stopped`, or `Failed` SHALL fail deterministically and SHALL not execute providers.

## 9. Lifecycle flows

### 9.1 Successful startup

```text
Created
  -> Bootstrapping
      -> bootstrap preparation
      -> provider register in insertion order
      -> provider boot in insertion order
  -> Booted
```

### 9.2 Successful run

```text
Booted -> Running
```

or:

```text
Created -> boot flow -> Booted -> Running
```

### 9.3 Successful shutdown

```text
Booted or Running
  -> Stopping
      -> provider shutdown in reverse order
  -> Stopped
```

### 9.4 Startup failure

```text
Created
  -> Bootstrapping
      -> failure
  -> Failed
```

No later startup stage SHALL execute after a registration or boot failure.

### 9.5 Shutdown with errors

All eligible providers SHALL receive a shutdown attempt. Errors SHALL be accumulated in deterministic order. The final terminal state and `BootResult` semantics SHALL be made explicit by the implementation specification; the failure collection SHALL never be discarded.

## 10. Result model

`BootResult` is the immutable outcome for every Kernel command.

It SHALL contain:

- final or relevant boot stage;
- start instant;
- completion instant;
- typed list of errors;
- typed list of warnings;
- first causal `Throwable`, when applicable.

It SHALL enforce:

- `list<BootError>` and `list<BootWarning>` semantics;
- no empty error list for failed results;
- no errors or cause for successful results;
- completion instant not earlier than start instant;
- defensive rejection of invalid construction.

The existing WP-003 factory methods remain the preferred creation API until superseded by an approved migration.

## 11. Failure semantics

### 11.1 Failure categories

Runtime Core distinguishes:

- invalid command or transition;
- bootstrap failure;
- provider registration failure;
- provider boot failure;
- provider shutdown failure;
- internal Kernel failure.

### 11.2 Cause preservation

The first thrown failure that causes command failure SHALL be retained as the primary cause. Subsequent failures SHALL be represented as structured `BootError` entries and SHALL NOT replace the first cause.

### 11.3 No hidden recovery

Runtime Core SHALL NOT retry, suppress, or automatically recover failed lifecycle operations unless a future normative specification introduces an explicit recovery policy.

## 12. Time boundary

Direct construction of `DateTimeImmutable` inside lifecycle coordination reduces deterministic testability. WP-201 SHALL define a minimal time abstraction or injectable instant provider before changing production timestamps.

This abstraction:

- is a Runtime Core testing boundary, not the future public `clock` capability;
- SHALL have no external dependency;
- SHALL preserve compatibility of existing timestamp return types;
- SHALL not expose global mutable time.

The future `clock` capability may adapt or replace the internal boundary through a separately approved design.

## 13. Public contracts

WP-201 SHALL preserve or evolve through compatible additions the following contracts:

- `ApplicationInterface`;
- `RuntimeInterface`;
- `KernelInterface`;
- `LifecycleInterface`;
- `BootstrapInterface`;
- `EnvironmentInterface`;
- `ServiceProviderInterface`.

New public methods SHALL be justified by an invariant or use case in this specification. Convenience methods without a stable architectural role SHALL remain internal.

## 14. Encapsulation rules

1. Public Application APIs SHALL not expose a mutable general-purpose container.
2. Runtime state mutation SHALL not be available to application or module code.
3. Providers SHALL interact through contracts and governed registration points.
4. Runtime Context values SHALL be immutable or replaced as a whole.
5. Exceptions SHALL retain previous causes where applicable.
6. DTOs and result objects SHALL reject invalid state at construction.
7. Runtime Core SHALL not depend on Builder, Persistence, HTTP, Console, or optional packages.

## 15. Capability boundary

EG-202 follows ADR-0005 but does not implement capability resolution.

Runtime Core MAY know only the capability access contract required to delegate later. It SHALL NOT:

- define built-in infrastructure implementations;
- use capability identifiers as arbitrary service keys;
- turn Runtime into a service locator;
- couple Kernel transitions to a concrete registry implementation.

The complete registration, provider selection, replacement, decoration, priority, multiplicity, and resolution model belongs to WP-202 and EG-201.

## 16. Observability boundary

Lifecycle activity SHALL be observable, but Runtime Core SHALL not depend on a concrete event dispatcher or logger.

WP-003 event objects remain valid integration artifacts. Dispatch behavior belongs to the Runtime Events Work Package. Before that integration, observability SHALL be achievable through explicit result objects, state inspection, and test collaborators.

## 17. Compatibility with WP-003

### 17.1 Preserved

WP-201 preserves:

- existing namespace structure;
- existing public Runtime states;
- existing Kernel command names;
- `BootResult`, `BootError`, and `BootWarning` concepts;
- provider ordering guarantees;
- first-cause preservation;
- best-effort shutdown principle;
- no global mutable state.

### 17.2 Refinements required

WP-201 SHALL review and, where necessary, correct:

- public exposure of `RuntimeInterface::transitionTo()` and `fail()` so Kernel remains the only lifecycle authority;
- support for clean shutdown from `Booted`;
- invalid command behavior for repeated or terminal operations;
- injectable time boundary;
- Runtime-to-Application ownership invariant;
- Runtime Context minimum contract;
- atomic transition validation and mutation.

Any public signature change SHALL be introduced only with a compatibility analysis, migration note, tests, and appropriate versioning.

## 18. Testing requirements

WP-201 implementation increments SHALL include at least:

- every legal transition;
- every illegal transition;
- boot success and each boot failure stage;
- run from `Created` and `Booted`;
- invalid run states;
- shutdown from `Booted` and `Running`;
- reverse shutdown order;
- multiple shutdown failures;
- first-cause preservation;
- immutable typed result lists;
- deterministic timestamps through the time boundary;
- terminal-state rejection;
- absence of optional infrastructure dependencies;
- compatibility tests for existing WP-003 public behavior.

PHPStan level 8 SHALL report zero errors.

## 19. Implementation increments

After approval of EG-202, WP-201 SHOULD proceed through bounded increments:

1. **WP-201-I1 — State and transition authority**  
   Internalize transition authority, complete legal transitions, and add compatibility tests.
2. **WP-201-I2 — Kernel command preconditions**  
   Define deterministic behavior for boot, run, shutdown, repetition, and terminal states.
3. **WP-201-I3 — Time and Runtime Context boundaries**  
   Introduce deterministic time and the minimal immutable Runtime Context model.
4. **WP-201-I4 — Result and failure hardening**  
   Complete result invariants, first-cause behavior, and shutdown aggregation.
5. **WP-201-I5 — Integration and product completion**  
   Documentation, migration notes, component metadata, full validation, and implementation review.

No increment SHALL introduce Capability Registry implementation, module discovery, or adapter-specific loops.

## 20. Acceptance criteria

EG-202 is ready for implementation when:

1. Runtime, Kernel, Application, Lifecycle, Environment, and Runtime Context responsibilities are unambiguous.
2. All legal and illegal Runtime transitions are explicitly governed.
3. Kernel is established as the sole lifecycle authority.
4. WP-003 compatibility constraints and required refinements are identified.
5. Startup, run, failure, and shutdown flows are deterministic.
6. Error accumulation and first-cause preservation are normative.
7. Time and observability boundaries avoid optional infrastructure dependencies.
8. Later Capability Registry, Module, Configuration, and Event work remains outside scope.
9. The architecture review records no unresolved blocking contradiction with EG-200, EG-201, ADR-0005, or WP-003.
10. SIF Builder validates the integrated documents with zero diagnostics.
