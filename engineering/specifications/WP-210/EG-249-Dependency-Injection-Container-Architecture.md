---
id: EG-249
title: Dependency Injection Container 2.0 Architecture
summary: Defines the architecture of the next-generation SIF dependency injection container, including definitions, aliases, lifetimes, scopes, autowiring, lazy services, contextual bindings, tags, resolution diagnostics, and compatibility boundaries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-210
tags:
  - foundation
  - container
  - dependency-injection
  - architecture
  - autowiring
depends_on:
  - EG-248
  - EG-240
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-249 — Dependency Injection Container 2.0 Architecture

## 1. Purpose

This specification defines the architecture of the next-generation SIF Dependency Injection Container.

The container SHALL provide explicit, deterministic, observable, and extensible service resolution while preserving compatibility with the current public access point and avoiding framework-wide coupling to concrete container internals.

WP-210-I1 is exclusively architectural.

It introduces no production PHP code and does not modify the current container implementation.

## 2. Architectural position

The container is part of the Foundation runtime.

Its dependency direction SHALL be:

```text
Application / Module
        |
        v
Container contracts
        |
        v
Container implementation
        |
        v
Service definitions and factories
```

Application modules MAY depend on container contracts.

The Core SHALL NOT require arbitrary services to depend on the concrete container.

Constructor injection remains preferred.

Service locator usage SHALL be restricted to composition boundaries and framework infrastructure.

## 3. Design principles

### 3.1 Explicit definitions

A service definition SHALL describe how a service is resolved.

A definition MAY contain:

- service identifier;
- implementation type;
- factory;
- arguments;
- aliases;
- lifetime;
- scope;
- lazy flag;
- tags;
- metadata;
- contextual bindings.

Definitions SHALL be immutable once compiled.

### 3.2 Deterministic resolution

Given the same definitions, scope, and resolution request, the container SHALL produce deterministic behavior.

Resolution order SHALL NOT depend on filesystem traversal, reflection order, or hash-map insertion side effects.

### 3.3 Contract-first design

The public surface SHALL be expressed through contracts.

The implementation MAY evolve independently as long as contract behavior is preserved.

### 3.4 Constructor injection first

Autowiring SHALL target constructor injection.

Property injection and method injection SHALL NOT be part of the default Core behavior.

They MAY be introduced later only through explicit opt-in extensions.

### 3.5 No hidden global container

The container SHALL NOT be globally mutable.

Static access MAY remain only through the existing compatibility façade, delegating to an explicitly constructed application container.

### 3.6 Observable resolution

Resolution failures SHALL expose:

- requested service;
- resolution path;
- failure category;
- optional original cause;
- safe diagnostic context.

The container SHALL NOT leak secrets or arbitrary object state through diagnostics.

### 3.7 Compatibility preservation

Existing container-facing APIs SHALL not be broken without:

- explicit compatibility mapping;
- migration documentation;
- deprecation period;
- SemVer-compliant release planning.

## 4. Service identifiers

A service identifier SHALL be represented as a non-empty opaque string.

Recommended identifiers include:

- interface names;
- class names;
- explicit application service names;
- qualified names for multiple implementations.

The Core SHALL NOT require all identifiers to be class names.

Examples:

```text
App\Contracts\MailerInterface
database.primary
cache.runtime
logger.audit
```

## 5. Service definitions

A service definition SHALL declare one resolution strategy.

Supported strategies SHOULD include:

1. concrete class;
2. factory callable;
3. existing instance;
4. alias;
5. lazy proxy definition;
6. contextual binding target.

A definition SHALL NOT contain multiple conflicting strategies.

Definition validation SHALL occur before runtime resolution whenever possible.

## 6. Lifetimes

The initial lifetime model SHOULD include:

- transient;
- singleton;
- scoped.

### 6.1 Transient

A new instance is produced for each resolution.

### 6.2 Singleton

A single instance is produced per container.

### 6.3 Scoped

A single instance is produced per active scope.

The Core SHALL NOT assume HTTP request semantics.

Scopes MAY represent:

- request;
- command;
- job;
- transaction;
- tenant;
- test;
- custom application boundary.

## 7. Scopes

A scope SHALL be explicit.

The architecture SHOULD define:

- scope identifier;
- parent scope;
- active scope context;
- scoped instance storage;
- scope disposal;
- nested scope policy.

A scoped service resolved without an active compatible scope SHALL fail predictably.

Scope creation SHALL not depend on global thread-local or process-global state.

## 8. Aliases

Aliases map one identifier to another.

Alias resolution SHALL:

- be deterministic;
- detect cycles;
- preserve the original requested identifier for diagnostics;
- support interface-to-implementation mappings;
- avoid duplicated service instances when aliasing singletons or scoped services.

An alias SHALL not create a new lifetime boundary.

## 9. Autowiring

Autowiring SHALL be optional and explicit at container or definition level.

The autowiring algorithm SHOULD:

1. inspect the target constructor;
2. resolve explicitly bound parameters first;
3. resolve contextual bindings second;
4. resolve class or interface type identifiers third;
5. use default parameter values when allowed;
6. fail when ambiguity remains.

Autowiring SHALL NOT:

- instantiate internal PHP classes automatically;
- guess scalar values;
- infer arbitrary configuration keys;
- resolve union types without an explicit binding;
- ignore missing mandatory dependencies;
- silently choose among multiple candidates.

## 10. Reflection policy

Reflection MAY be used by the container implementation for constructor analysis.

Reflection SHALL be limited to resolution metadata.

The container SHALL NOT:

- mutate private properties;
- invoke arbitrary lifecycle methods by convention;
- inspect application state;
- persist reflection metadata outside an explicit cache boundary.

Reflection metadata SHOULD be cacheable.

## 11. Contextual bindings

A contextual binding selects a dependency based on the consumer.

Conceptually:

```text
when ConsumerA needs LoggerInterface use AuditLogger
when ConsumerB needs LoggerInterface use RuntimeLogger
```

Contextual bindings SHALL be explicit.

Resolution precedence SHOULD be:

1. argument override;
2. contextual binding;
3. direct definition;
4. alias;
5. autowiring candidate;
6. default value;
7. failure.

Contextual bindings SHALL not mutate global definitions.

## 12. Tagged services

A definition MAY declare one or more tags.

Tags SHALL support:

- ordered service discovery;
- extension points;
- event listeners;
- command handlers;
- serializers;
- validators;
- middleware;
- providers.

Tag retrieval SHALL be deterministic.

Tags MAY include metadata such as priority.

Priority ordering SHALL be stable and explicitly defined.

## 13. Lazy services

Lazy services defer expensive construction until first real use.

The architecture MAY support lazy resolution through:

- proxy;
- closure;
- lazy reference;
- generated wrapper.

Lazy behavior SHALL be explicit in the definition.

The Core SHALL NOT silently make every service lazy.

Lazy services SHALL preserve:

- declared type compatibility;
- lifetime semantics;
- scope semantics;
- failure transparency.

Proxy generation strategy belongs to later implementation increments.

## 14. Factories

A factory MAY create a service.

A factory SHOULD receive only explicit dependencies.

Factories SHALL NOT depend on a globally accessible container unless they are framework-level composition factories.

The architecture SHOULD support:

- callable factories;
- factory service plus method;
- invokable factory objects.

Factory exceptions SHALL be wrapped or translated into stable container failures while preserving the original cause.

## 15. Existing instances

The container MAY register an existing instance.

Existing instances:

- are inherently singleton within the registering container;
- SHALL not be cloned;
- SHALL not be disposed unless explicit ownership is transferred;
- SHALL preserve identity through aliases.

## 16. Circular dependency detection

The container SHALL detect:

- direct cycles;
- indirect cycles;
- alias cycles;
- factory resolution cycles;
- contextual binding cycles.

A cycle failure SHALL expose the full safe resolution path.

Example:

```text
A -> B -> C -> A
```

The container SHALL fail before exhausting memory or recursion depth.

## 17. Resolution context

Every resolution SHOULD carry an immutable context containing:

- original requested identifier;
- current identifier;
- consumer identifier;
- parameter name;
- active scope;
- resolution path;
- optional overrides.

Resolution context SHALL remain internal or exposed through a stable diagnostic abstraction.

It SHALL not reuse `ExecutionContextInterface` as a service locator concern.

Execution Context and container resolution context are distinct concepts.

## 18. Parameters and scalar values

Scalar constructor arguments SHALL require explicit binding.

The container MAY support parameter definitions such as:

```text
app.name
database.timeout
cache.prefix
```

Configuration lookup SHALL be performed through explicit parameter providers or bindings.

The container SHALL not read environment variables directly.

## 19. Compilation

The architecture SHOULD support a compilation phase.

Compilation MAY:

- validate definitions;
- resolve aliases;
- detect duplicate identifiers;
- detect obvious cycles;
- precompute reflection metadata;
- index tags;
- normalize contextual bindings;
- produce immutable runtime definitions.

The first implementation MAY remain interpreted, but public contracts SHALL allow future compilation without breaking consumers.

## 20. Container hierarchy

Child containers MAY be supported later.

The initial architecture SHOULD prefer scopes over unrestricted child containers.

If container hierarchy is introduced, it SHALL define:

- parent lookup;
- override policy;
- singleton ownership;
- disposal;
- alias behavior;
- scope inheritance.

No implicit parent mutation is allowed.

## 21. Disposal and lifecycle

Services MAY require deterministic disposal.

The architecture SHOULD support an explicit disposable contract in a later increment.

Disposal SHALL:

- occur in reverse construction order where applicable;
- respect scopes;
- continue after individual disposal failures;
- collect diagnostics;
- never occur implicitly during arbitrary garbage collection assumptions.

WP-210 SHALL not automatically integrate service disposal with Runtime shutdown until a separate integration increment is approved.

## 22. Error taxonomy

The container SHOULD define typed failures for:

- invalid service identifier;
- duplicate definition;
- missing definition;
- unresolvable dependency;
- ambiguous dependency;
- circular dependency;
- alias cycle;
- invalid factory;
- invalid scope;
- missing active scope;
- lazy proxy failure;
- service creation failure;
- definition compilation failure.

All failures SHOULD preserve the original cause when one exists.

## 23. Framework integration

The container SHALL integrate incrementally with:

- `Framework.php`;
- Application;
- Kernel;
- Service Providers;
- Modules;
- Runtime scopes;
- Configuration;
- Event Dispatcher;
- Persistence adapters.

No subsystem SHALL be migrated automatically during WP-210-I1.

## 24. Existing container compatibility

The existing container behavior SHALL be inventoried before implementation.

WP-210 SHALL provide an explicit compatibility matrix covering:

- `set`;
- `get`;
- `has`;
- factory registration;
- instance registration;
- aliases;
- current exception behavior;
- existing tests;
- `Framework.php` access.

Legacy behavior MAY be implemented through an adapter around Container 2.0 during migration.

## 25. Security

The container SHALL avoid exposing:

- environment values;
- credentials;
- connection strings;
- secret constructor arguments;
- complete object dumps;
- sensitive configuration;
- arbitrary closure internals.

Diagnostics SHALL identify dependency paths without serializing service state.

## 26. Explicit exclusions

WP-210-I1 does not implement:

- production container code;
- autowiring;
- proxy generation;
- reflection cache;
- scopes;
- tags;
- contextual bindings;
- compiled container;
- service disposal;
- Framework integration;
- migration of existing services;
- PSR-11 adapter;
- attribute-based injection;
- property injection;
- method injection.

## 27. Increment plan

WP-210 SHOULD proceed through governed increments:

1. **WP-210-I1** — architecture and compatibility boundaries;
2. **WP-210-I2** — identifiers, definitions, lifetimes, aliases, and core contracts;
3. **WP-210-I3** — deterministic resolution engine and cycle detection;
4. **WP-210-I4** — constructor autowiring and scalar bindings;
5. **WP-210-I5** — scopes and scoped lifetimes;
6. **WP-210-I6** — contextual bindings and tagged services;
7. **WP-210-I7** — lazy services, diagnostics, and compilation model;
8. **WP-210-I8** — compatibility adapter, vertical integration, and product completion.

## 28. Acceptance criteria

WP-210-I1 is accepted when:

- service identifiers are technology-neutral;
- definitions and lifetimes are explicit;
- scope behavior is defined;
- aliases preserve identity;
- autowiring precedence is explicit;
- scalar injection requires explicit binding;
- contextual bindings are deterministic;
- tag ordering is defined;
- lazy behavior is opt-in;
- cycle detection is mandatory;
- diagnostics preserve safe resolution paths;
- compatibility with the existing container is planned;
- Builder diagnostics remain zero;
- governed generation is deterministic.
