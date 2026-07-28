---
id: WP-212-I1-REVIEW
title: WP-212-I1 Module Registry 2.0 Architecture Review
summary: Reviews the deterministic, dependency-aware, lifecycle-safe, capability-integrated, and compatibility-first architecture proposed for the SIF Module Registry 2.0.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-212
tags:
  - foundation
  - modules
  - registry
  - architecture
  - compatibility
  - review
depends_on:
  - EG-265
  - EG-202
  - EG-205
  - EG-213
  - EG-249
  - EG-257
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-212-I1 — Module Registry 2.0 Architecture Review

## Scope

WP-212-I1 defines the architecture and compatibility boundaries of Module Registry 2.0.

It adds no production PHP code and does not alter Runtime, Service Provider, Container, Configuration, Event Dispatcher, Capability Registry, Context, Audit, or Persistence behavior.

## Sequence correction

Runtime Events are not an appropriate WP-212 target because Event Dispatcher Core and Runtime observation were already delivered by WP-205 and WP-206.

The Module Registry is the remaining Runtime Foundation integration boundary explicitly prepared but not completed.

Assigning WP-212 to Module Registry 2.0 therefore avoids subsystem duplication and advances the approved architecture coherently.

## Baseline review

The architecture correctly recognizes the existing foundation:

- Runtime state and lifecycle orchestration;
- deterministic Service Provider lifecycle;
- capability registration;
- event dispatch and observation;
- Execution Context;
- Audit;
- Persistence;
- Container 2.0;
- Configuration 2.0.

The proposed registry composes these systems rather than replacing them.

## Responsibility review

The specification maintains clear boundaries:

- a module descriptor declares identity, dependencies, capabilities, and contributions;
- the registry owns controlled registration;
- the resolver owns graph validation and deterministic ordering;
- the resolved plan owns immutable execution intent;
- Runtime retains lifecycle authority;
- Service Providers retain registration, boot, and shutdown behavior;
- Container retains service resolution;
- Configuration retains source composition and freezing;
- Capability Registry retains capability publication.

This separation prevents the module abstraction from becoming a second container or a second Runtime.

## Identity and descriptor review

A stable `ModuleId` is necessary because package name, namespace, path, and display name are different concerns.

Immutable descriptors are appropriate because dependency resolution must not execute module behavior or observe mutable runtime state.

Rejecting network access, service resolution, and uncontrolled filesystem traversal during descriptor construction improves determinism and security.

## Discovery review

Explicit bounded discovery is the correct default.

Filesystem-wide scanning would create ordering, security, deployment, and reproducibility risks.

Future Composer or manifest adapters can remain infrastructure components that produce normalized descriptors without changing the Module Core.

## Dependency review

The required, optional, conflict, and capability requirement model is sufficient for the initial architecture.

Resolution before contribution is essential: no module should modify configuration, container definitions, providers, or capabilities until the entire enabled graph is proven valid.

Deterministic topological ordering with stable tie-breakers is appropriate.

## Resolved-plan review

An immutable resolved plan provides a strong integration boundary.

It enables:

- inspection before activation;
- reproducible ordering;
- safe diagnostics;
- deterministic shutdown order;
- future canonical fingerprinting;
- focused Runtime integration tests.

The plan correctly excludes service instances, closures, secrets, and arbitrary object dumps from safe export.

## Enablement review

Presence on disk must not imply enablement.

Explicit application, configuration, deployment, or test policy is safer and more reproducible.

The architecture correctly treats disabling a required dependency as a resolution failure for enabled dependents.

## Configuration review

Module-owned namespaces prevent uncontrolled cross-module mutation.

Using Configuration 2.0 source and schema contracts preserves provenance, redaction, deterministic precedence, and freeze semantics.

Module defaults should remain lower precedence than explicit application configuration unless a separately approved policy states otherwise.

## Container review

Explicit definition contribution is preferable to exposing the mutable container to arbitrary module code.

Applying contributions in resolved module order preserves determinism.

The architecture correctly forbids hidden configuration injection and dependency on concrete container internals.

## Service Provider and Runtime review

The existing provider lifecycle remains authoritative.

The registry contributes providers in resolved module order and does not redefine provider semantics.

Reverse shutdown and current failure behavior remain compatible.

This is the lowest-risk integration strategy.

## Capability review

Separating descriptor-level requirements from runtime capability publication is correct.

Capabilities needed to validate the graph must be declared before activation, while runtime capability instances should be published only after the owning module reaches the required lifecycle stage.

## Event, Context, and Audit review

Event integration is properly observational.

Listeners must not rewrite module plans or obtain lifecycle transition authority.

Execution Context remains explicit rather than global.

Audit remains optional and sink-neutral.

These boundaries align with WP-205 through WP-208.

## Compatibility review

Provider-only applications remain valid.

Module Registry adoption is additive and opt-in.

This protects existing Runtime and provider consumers while allowing a staged migration.

A synthetic compatibility contribution may wrap explicitly mapped providers, but the framework should not invent identities for existing third-party code.

## Security review

The architecture correctly separates availability, enablement, compatibility, trust, and successful activation.

It does not claim to sandbox or verify untrusted modules.

This avoids presenting a registry as a security boundary it cannot enforce.

## Diagnostic review

Typed exceptions and stable diagnostic families are appropriate.

Messages must remain secret-safe and path-neutral.

Deterministic diagnostic order is important for tests, generated reports, and institutional traceability.

## Increment review

The proposed sequence is coherent:

1. architecture;
2. identity, version, descriptors, and contracts;
3. registry and catalog;
4. dependency resolution;
5. enablement and resolved plans;
6. configuration, container, and capability contributions;
7. Runtime/provider integration, events, diagnostics, and fingerprints;
8. reference integration and product completion.

Each increment has a sufficiently narrow responsibility and preserves the SIF governance cycle.

## Risk review

Primary risks are:

1. duplicating Service Providers;
2. creating a service locator;
3. hidden discovery;
4. graph non-determinism;
5. premature contribution execution;
6. dependency cycles and conflicts;
7. cross-module configuration mutation;
8. container internals leakage;
9. premature capability publication;
10. listener authority escalation;
11. diagnostic data leakage;
12. provider compatibility regression;
13. conflating installation with composition;
14. treating untrusted code as safe.

The proposed architecture addresses these risks through declarative metadata, immutable plans, deterministic resolution, explicit adapters, lifecycle ownership, safe diagnostics, and compatibility-first adoption.

## Recommendation

Approve WP-212-I1.

Continue with WP-212-I2, limited to:

- `ModuleId`;
- `ModuleVersion`;
- dependency and conflict value objects;
- immutable `ModuleDescriptor`;
- module descriptor contracts;
- typed descriptor-validation exceptions;
- focused unit tests.

WP-212-I2 should not implement registry mutation, graph resolution, enablement policy, Runtime integration, provider execution, configuration or container contributions, caching, installation, or discovery adapters.
