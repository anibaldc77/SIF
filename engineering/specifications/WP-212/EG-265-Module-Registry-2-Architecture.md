---
id: EG-265
title: Module Registry 2.0 Architecture
summary: Defines the compatible, deterministic, dependency-aware, capability-integrated, and lifecycle-safe architecture for discovering, describing, resolving, registering, booting, and shutting down SIF modules.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-212
tags:
  - foundation
  - modules
  - registry
  - lifecycle
  - dependencies
  - architecture
depends_on:
  - EG-202
  - EG-205
  - EG-206
  - EG-213
  - EG-226
  - EG-233
  - EG-249
  - EG-257
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-265 — Module Registry 2.0 Architecture

## 1. Purpose

WP-212 defines the architecture of the SIF Module Registry 2.0.

The subsystem SHALL provide deterministic module description, dependency resolution, registration, lifecycle participation, capability publication, diagnostics, and controlled integration with Runtime, Container 2.0, Configuration 2.0, Event Dispatcher, Execution Context, and Audit.

WP-212-I1 is exclusively architectural. It introduces no production PHP code and does not modify current Runtime behavior.

## 2. Existing baseline

SIF already provides:

- Runtime state and lifecycle orchestration;
- ordered Service Provider registration, boot, and shutdown;
- capability registration and lookup;
- Event Dispatcher Core and Runtime observation;
- Execution Context contracts;
- Audit composition and event-driven emission;
- storage-neutral persistence contracts;
- Container 2.0 definitions, scopes, tags, contextual bindings, lazy references, diagnostics, and compatibility integration;
- Configuration 2.0 sources, schemas, secret safety, snapshots, caching, and bootstrap integration.

The Runtime Foundation explicitly prepared integration points for modules but intentionally did not implement a complete module registry.

WP-212 SHALL fill that boundary without replacing Service Providers, Capability Registry, Runtime, or the container.

## 3. Architectural goals

Module Registry 2.0 SHALL support:

1. immutable module identity and metadata;
2. explicit module descriptors;
3. deterministic registration order;
4. required and optional dependencies;
5. version and compatibility constraints;
6. cycle and conflict detection;
7. enablement and disablement policies;
8. explicit module composition;
9. Service Provider contribution;
10. Configuration namespace contribution;
11. Container definition contribution;
12. capability publication;
13. lifecycle-safe boot and shutdown;
14. structured diagnostics;
15. immutable resolved module plans;
16. reproducible fingerprints;
17. migration from explicit provider wiring without breaking existing applications.

## 4. Non-goals

WP-212 SHALL NOT implement:

- package installation or dependency download;
- Composer replacement;
- arbitrary plugin execution from untrusted sources;
- filesystem-wide automatic discovery;
- remote module marketplaces;
- hot reload of production modules;
- process isolation or sandboxing;
- HTTP routing, console routing, queues, scheduling, assets, or migrations;
- module-specific business logic;
- automatic semantic-version upgrades;
- hidden mutation of global Runtime state.

These concerns require separate governed work packages.

## 5. Dependency direction

The intended dependency direction is:

```text
Application composition root
          |
          v
Module Registry contracts
          |
          v
Module descriptors and resolved plans
          |
          +----> Configuration contribution contracts
          +----> Container contribution contracts
          +----> Service Provider contribution contracts
          +----> Capability publication contracts
          |
          v
Runtime integration adapter
```

The Module Core SHALL NOT depend on application modules.

Concrete modules SHALL depend on Foundation contracts, not on the concrete registry implementation.

Runtime SHALL interact with modules through an explicit integration contract or resolved plan, not through filesystem discovery.

## 6. Module identity

A `ModuleId` value object SHALL provide stable module identity.

Rules SHALL include:

- non-empty canonical string;
- case-sensitive comparison;
- restricted portable character set;
- no whitespace;
- no path separators;
- no implicit aliasing;
- deterministic string representation.

A module identifier SHALL represent logical identity, not installation path, PHP namespace, vendor package name, or display title.

Compatibility adapters MAY map package names or legacy identifiers to `ModuleId`, but canonical identity SHALL remain explicit.

## 7. Module version

A `ModuleVersion` value object SHOULD represent the module's declared version.

The initial model SHOULD support semantic-version-compatible normalized values without requiring a third-party runtime dependency.

Version comparison SHALL be deterministic.

Invalid or ambiguous versions SHALL fail during descriptor construction.

The module registry SHALL NOT infer versions from source-control state or mutable filesystem timestamps.

## 8. Module descriptor

A module descriptor SHALL be immutable and SHOULD include:

- module identifier;
- version;
- human-readable name;
- optional description;
- required module dependencies;
- optional module dependencies;
- declared conflicts;
- required framework capabilities;
- provided capabilities;
- configuration namespace ownership;
- Service Provider class references or factories;
- optional container contribution reference;
- optional lifecycle participation metadata;
- safe diagnostic metadata.

Descriptors SHALL contain declarative metadata only.

Descriptor construction SHALL NOT:

- access the network;
- mutate Runtime state;
- resolve container services;
- execute module boot logic;
- reveal secrets;
- perform uncontrolled filesystem traversal.

## 9. Module contracts

The architecture SHOULD distinguish:

### 9.1 Descriptor contract

Returns immutable module metadata.

### 9.2 Module contract

Represents a concrete module contribution boundary.

A module MAY expose explicit methods or collaborators for:

- descriptor retrieval;
- configuration contribution;
- container contribution;
- provider contribution;
- capability contribution.

The module object SHALL NOT become a service locator.

### 9.3 Registry read contract

Provides module lookup and plan inspection without mutation.

### 9.4 Mutable registration contract

Allows controlled registration before resolution or freeze.

### 9.5 Resolver contract

Transforms registered descriptors and enablement policy into an immutable resolved module plan.

### 9.6 Runtime integration contract

Applies a resolved plan to the existing bootstrap lifecycle through explicit composition.

## 10. Registration

Module registration SHALL be explicit.

The initial implementation SHOULD support:

- direct module objects;
- direct immutable descriptors with contribution factories;
- application composition factory registration;
- deterministic test registration.

Registration order SHALL be preserved as a stable tie-breaker only after dependency and priority rules.

Duplicate canonical identifiers SHALL fail with a typed exception unless an explicit replacement policy is approved in a later increment.

A registry SHALL become immutable after resolution or publication of its resolved plan.

## 11. Discovery policy

Core discovery SHALL be explicit and bounded.

The registry MAY accept descriptors produced by external discovery adapters, but SHALL NOT itself scan arbitrary directories.

Potential future adapters include:

- Composer metadata;
- application manifest files;
- generated module manifests;
- deployment profiles;
- test fixtures.

Every adapter SHALL produce the same normalized descriptor model.

Discovery source precedence and trust policy SHALL be explicit.

## 12. Dependency model

A module MAY declare:

- required dependencies;
- optional dependencies;
- conflicts;
- framework capability requirements.

A dependency SHOULD include:

- target `ModuleId`;
- optional version constraint;
- requirement kind;
- safe diagnostic context.

Required dependencies SHALL be present and compatible.

Optional dependencies SHALL influence ordering only when present and compatible.

Conflicts SHALL fail resolution before any module contribution is applied.

Dependency resolution SHALL NOT instantiate module services.

## 13. Deterministic resolution

The resolver SHALL produce one deterministic topological order.

Ordering SHALL consider, in order:

1. required dependency edges;
2. present optional dependency edges;
3. explicit module priority when approved by contract;
4. stable registration order;
5. canonical identifier as final deterministic tie-breaker if required.

The resolver SHALL detect:

- missing required modules;
- incompatible versions;
- dependency cycles;
- declared conflicts;
- duplicate identifiers;
- unsatisfied capability requirements;
- invalid configuration namespace ownership;
- ambiguous contribution order.

Equivalent input SHALL produce equivalent resolved order and fingerprint.

## 14. Resolved module plan

Resolution SHALL produce an immutable `ResolvedModulePlan`.

The plan SHOULD expose:

- ordered module descriptors;
- enabled modules;
- disabled modules and safe reasons;
- dependency edges;
- capability requirements and providers;
- provider contribution order;
- configuration contribution order;
- container contribution order;
- lifecycle shutdown order;
- diagnostics;
- canonical fingerprint.

The plan SHALL NOT expose secrets, service instances, closures in exported form, or arbitrary object dumps.

Shutdown order SHALL be the reverse of successful activation order unless a later explicit contract states otherwise.

## 15. Enablement policy

Module enablement SHALL be explicit and deterministic.

Enablement MAY be derived from:

- application composition;
- a validated Configuration 2.0 namespace;
- deployment profile;
- test policy.

A module SHALL NOT silently enable itself by merely being present on disk.

Disabling a required dependency SHALL cause resolution failure for enabled dependents.

Policy evaluation SHALL occur before module contributions are applied.

## 16. Configuration integration

Modules MAY contribute configuration through Configuration 2.0 source contracts.

Each module SHALL own an explicit configuration namespace.

Rules SHALL include:

- no cross-module namespace mutation by default;
- deterministic contribution precedence;
- schema registration before final validation;
- secret classification inherited from Configuration 2.0;
- provenance identifying the contributing module without exposing values;
- no direct mutation after configuration freeze.

Module configuration defaults SHALL remain observable and SHALL NOT override application-level values unless the approved precedence policy permits it.

## 17. Container integration

Modules MAY contribute definitions through explicit Container 2.0 contracts.

The registry SHALL NOT expose the mutable container to arbitrary descriptor code.

Container contributions SHALL be applied in resolved module order.

Modules SHOULD use:

- service definitions;
- aliases;
- tags;
- contextual bindings;
- explicit factories;
- scope declarations.

Modules SHALL NOT depend on container internals or infer configuration through constructor parameter names.

Container compilation or freeze SHALL occur only after all enabled module contributions are applied.

## 18. Service Provider integration

Modules MAY contribute existing `ServiceProviderInterface` implementations.

The Module Registry SHALL preserve the established Runtime provider lifecycle:

1. provider registration in deterministic order;
2. provider boot in deterministic order;
3. provider shutdown in reverse order;
4. register or boot failure stops successful startup;
5. shutdown continues while accumulating errors.

The registry SHALL compose providers; it SHALL NOT replace their responsibilities.

A provider contributed by a disabled or unresolved module SHALL never execute.

## 19. Capability integration

Modules MAY declare required and provided capabilities.

Capability publication SHALL use the existing Capability Registry boundary.

A capability SHALL be published only after its owning module reaches the required activation stage.

Capability requirements needed for resolution SHALL use descriptor-level declarations, not runtime service lookup.

Capability identifiers and module identifiers SHALL remain distinct value domains.

## 20. Event integration

Module lifecycle observation MAY use the existing Event Dispatcher and Runtime observation infrastructure.

Potential events include:

- module plan resolved;
- module activation started;
- module activated;
- module activation failed;
- module shutdown started;
- module shutdown completed;
- module shutdown failed.

Events SHALL be immutable, secret-safe, and observational.

Listeners SHALL NOT gain hidden authority to rewrite the resolved plan or bypass Runtime transitions.

Event dispatch failure policy SHALL be explicit and aligned with WP-205/WP-206 behavior.

## 21. Execution Context integration

Module operations MAY receive or derive Execution Context through explicit contracts.

The registry SHALL NOT discover context from globals.

Diagnostics and events MAY include safe context identifiers, subject to existing redaction rules.

Context propagation SHALL remain separate from dependency resolution.

## 22. Audit integration

Security-sensitive module operations MAY emit audit records through the existing Audit subsystem.

Examples include:

- enablement policy changes;
- module replacement attempts;
- conflict overrides if ever supported;
- activation failure affecting application startup.

Audit integration SHALL be optional, explicit, and event-driven where practical.

The Module Registry SHALL NOT depend on a concrete audit sink.

## 23. Lifecycle state model

The module subsystem SHOULD distinguish at least:

- collecting;
- resolved;
- contributing;
- registered;
- booted;
- shutting down;
- stopped;
- failed.

Transitions SHALL be controlled and deterministic.

Repeated resolution with mutation between runs SHALL not be permitted on the same frozen registry instance.

Repeated shutdown SHOULD be idempotent where no new activation occurred.

## 24. Failure taxonomy

Typed failures SHOULD include:

- invalid module identifier;
- invalid module version;
- invalid descriptor;
- duplicate module;
- module not found;
- missing dependency;
- incompatible dependency version;
- circular dependency;
- module conflict;
- unsatisfied capability requirement;
- namespace ownership conflict;
- invalid registry state;
- contribution failure;
- activation failure;
- shutdown failure.

Exceptions SHALL contain stable codes and safe identifiers.

They SHALL NOT include configuration values, secrets, arbitrary serialized objects, stack traces in public messages, or host-specific absolute paths.

## 25. Diagnostics

Structured diagnostics SHOULD include:

- stable code;
- severity;
- phase;
- module identifier when safe;
- related dependency identifier when safe;
- concise message;
- safe metadata.

Suggested code families:

- `MOD-1xx` descriptor and registration;
- `MOD-2xx` dependency resolution;
- `MOD-3xx` contribution and composition;
- `MOD-4xx` lifecycle integration;
- `MOD-5xx` compatibility and migration.

Diagnostic ordering SHALL be deterministic.

## 26. Canonical serialization and fingerprint

A resolved module plan SHOULD support canonical safe serialization.

The canonical representation MAY include:

- module IDs and versions;
- dependency graph;
- enablement state;
- declared capability IDs;
- contribution ordering;
- policy version.

It SHALL exclude:

- secrets;
- service instances;
- closures;
- mutable object state;
- absolute paths;
- timestamps that would break reproducibility unless explicitly normalized.

A fingerprint SHALL be derived from canonical safe data using an explicit algorithm identifier.

## 27. Compatibility

WP-212 SHALL preserve existing applications that register Service Providers directly.

Module Registry participation SHALL be additive and opt-in during migration.

The following behavior SHALL remain valid:

- direct provider collections;
- applications with no modules;
- current Runtime boot order;
- current Capability Registry use;
- existing Event Dispatcher behavior;
- current Configuration and Container compatibility APIs.

A compatibility adapter MAY expose direct providers as one synthetic application contribution, but SHALL NOT invent module identity for third-party code without an explicit mapping.

## 28. Security and trust

Module presence SHALL NOT imply trust.

The architecture SHALL distinguish:

- known descriptor source;
- enabled policy;
- compatibility validity;
- successful activation.

The registry SHALL NOT evaluate downloaded code, verify package signatures, or sandbox modules in WP-212.

Applications requiring untrusted plugins need a separate isolation architecture.

Safe export SHALL avoid leaking environment structure, secret names where sensitive, and host paths.

## 29. Concurrency and process model

The initial Module Registry SHALL assume deterministic single-process composition during application bootstrap.

Concurrent mutation SHALL not be supported.

Immutable descriptors and resolved plans SHOULD be safe to read concurrently after publication, subject to PHP runtime constraints.

Distributed module coordination is outside WP-212.

## 30. Performance

Resolution SHOULD be linear or near-linear relative to modules and dependency edges.

The architecture SHALL avoid repeated reflection and repeated graph construction during one bootstrap.

Resolved plans MAY be cached in a later increment only when:

- canonical input fingerprinting exists;
- cache integrity is verified;
- stale plans fail safely;
- cache use remains optional.

WP-212-I1 does not approve runtime cache implementation.

## 31. Testing strategy

Later increments SHALL provide tests for:

- identity and descriptor invariants;
- duplicate rejection;
- required and optional dependencies;
- deterministic topological ordering;
- cycle detection;
- version incompatibility;
- conflict detection;
- capability requirements;
- enablement policy;
- configuration namespace ownership;
- container contribution order;
- provider registration, boot, and reverse shutdown;
- partial activation failure;
- diagnostic redaction;
- canonical fingerprint reproducibility;
- compatibility with provider-only applications;
- PHPStan level 8.

Tests SHALL not depend on network access, wall-clock timing, random order, or host-specific paths.

## 32. Proposed increments

WP-212 SHOULD proceed through governed increments:

1. **WP-212-I1** — architecture and compatibility boundaries;
2. **WP-212-I2** — module identity, version, descriptor, and core contracts;
3. **WP-212-I3** — registry, registration invariants, and immutable catalog;
4. **WP-212-I4** — deterministic dependency resolver, constraints, conflicts, and cycle detection;
5. **WP-212-I5** — enablement policy and immutable resolved module plan;
6. **WP-212-I6** — Configuration 2.0, Container 2.0, and capability contribution contracts;
7. **WP-212-I7** — Service Provider and Runtime lifecycle integration, events, diagnostics, and safe fingerprints;
8. **WP-212-I8** — reference integration, compatibility validation, migration guidance, and product completion.

Each increment SHALL pass the complete repository quality gate before approval.

## 33. Risks

Primary risks include:

1. duplicating the Service Provider subsystem;
2. turning modules into service locators;
3. hidden filesystem discovery;
4. non-deterministic dependency order;
5. executing contributions before graph validation;
6. cyclic or incompatible modules;
7. cross-module configuration mutation;
8. leaking mutable container internals;
9. publishing capabilities before activation;
10. event listeners mutating lifecycle authority;
11. secret leakage through diagnostics;
12. breaking provider-only applications;
13. conflating package installation with runtime composition;
14. accepting untrusted module code as safe.

The architecture mitigates these risks through declarative descriptors, immutable plans, explicit composition, staged lifecycle integration, typed failures, safe diagnostics, and compatibility-first migration.

## 34. Acceptance criteria

WP-212-I1 is accepted when:

- module responsibilities are distinct from providers, capabilities, Runtime, and Container;
- identity, descriptor, dependency, enablement, and resolved-plan boundaries are defined;
- deterministic ordering and cycle detection are normative;
- Configuration 2.0 and Container 2.0 integration are explicit;
- provider lifecycle compatibility is preserved;
- filesystem autodiscovery and package installation remain excluded;
- diagnostics and security boundaries are defined;
- the eight governed increments are established;
- no production PHP code is added;
- repository documentation generation and validation succeed.

## 35. Recommendation

Approve WP-212-I1 and continue with WP-212-I2, limited to:

- `ModuleId`;
- `ModuleVersion`;
- dependency and conflict value objects;
- immutable `ModuleDescriptor`;
- core module contracts;
- descriptor validation exceptions;
- focused unit tests.

WP-212-I2 SHALL NOT yet implement registry mutation, graph resolution, enablement policy, Runtime integration, provider execution, configuration contribution, container contribution, caching, or filesystem discovery.
