---
id: EG-289
title: Resource Management and Asset Foundation Architecture
summary: Defines the architecture, contracts, security boundaries, deterministic resolution model, package structure and incremental delivery plan for SIF resource and asset management.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-215
tags:
  - foundation
  - resources
  - assets
  - architecture
  - security
  - localization
depends_on:
  - EG-288
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-289 — Resource Management and Asset Foundation Architecture

## 1. Purpose

WP-215 defines the next-generation resource-management foundation for SIF.

The subsystem SHALL provide explicit, deterministic and secure discovery of framework and module resources without coupling the Foundation runtime to HTTP, template engines, package managers or deployment-specific public directories.

WP-215-I1 is architectural only. It introduces no production PHP code and does not modify existing runtime behavior.

## 2. Scope

The architecture covers the following resource families:

- stylesheets;
- JavaScript files;
- images;
- fonts;
- locale catalogues;
- translation resources;
- generic immutable package resources.

The initial Core SHALL model, register, locate, validate and publish resource descriptions. Transformation, bundling and network delivery remain external concerns unless introduced by a later governed increment.

## 3. Architectural position

```text
Application / Module
        |
        v
Resource contracts
        |
        v
Registry and deterministic resolver
        |
        v
Validated resource roots and descriptors
        |
        v
Optional publisher / manifest adapters
```

Modules MAY contribute resources through contracts.

The Foundation SHALL NOT depend on module implementations.

## 4. Design principles

### 4.1 Explicit registration

Resource roots and descriptors SHALL be registered explicitly.

The Core SHALL NOT scan the complete repository or infer resources from arbitrary filesystem state.

### 4.2 Deterministic resolution

Given the same registrations and request, resolution SHALL produce the same result.

Resolution order SHALL be governed by:

1. exact resource identifier;
2. explicitly declared namespace;
3. priority;
4. registration order as deterministic tie-breaker.

### 4.3 Filesystem confinement

All filesystem-backed resources SHALL remain confined to validated roots.

The implementation SHALL reject:

- parent-directory traversal;
- absolute paths where a relative path is required;
- null bytes;
- root escapes after canonicalization;
- symbolic-link escapes when canonical path verification is available.

### 4.4 Storage neutrality

The public model SHALL not assume that every resource is a local file.

A descriptor MAY represent:

- a local file;
- an embedded resource;
- a generated immutable artifact;
- an externally published resource reference.

Concrete adapters SHALL implement provider-specific behavior.

### 4.5 Immutability

Identifiers, roots, descriptors, resolution results and manifests SHALL be immutable value models.

Registries MAY be mutable during composition and SHALL support an immutable compiled representation.

### 4.6 No hidden global state

The subsystem SHALL NOT use global mutable registries, process-wide current directories or ambient application instances.

### 4.7 Observable failures

Failures SHALL expose typed exceptions and structured context suitable for integration with WP-213 logging and WP-214 error handling.

## 5. Core model

The architecture anticipates the following value objects:

- `ResourceIdentifier`;
- `ResourceNamespace`;
- `ResourceType`;
- `ResourcePath`;
- `ResourceRoot`;
- `ResourcePriority`;
- `ResourceDescriptor`;
- `ResolvedResource`;
- `ResourceFingerprint`.

Identifiers and namespaces SHALL be case-sensitive opaque values unless a future specification explicitly defines normalization.

## 6. Resource types

The initial vocabulary SHALL include:

```text
stylesheet
script
image
font
locale
translation
generic
```

The vocabulary SHALL be extensible without requiring the Foundation to understand provider-specific semantics.

## 7. Registration model

A resource registration SHALL declare:

- identifier;
- namespace;
- type;
- source descriptor;
- optional logical version;
- priority;
- scalar-or-null metadata;
- registration order.

Duplicate identifiers within the same namespace SHALL fail unless an explicit override policy is configured.

Silent replacement is forbidden.

## 8. Resolution

The resolver SHALL accept a structured request and return either:

- one immutable resolved resource;
- an immutable ordered collection;
- a typed not-found or ambiguity failure.

The Core SHALL not read file contents during identity resolution unless content verification is explicitly requested.

## 9. Module contributions

Modules MAY contribute resources through a provider contract.

Contribution SHALL occur during controlled composition and SHALL preserve:

- module identity;
- registration order;
- source ownership;
- deterministic override rules;
- unload-independent compiled manifests.

The module subsystem SHALL not be required to know concrete registry internals.

## 10. Localization boundary

Locale and translation resources are part of discovery and description, but translation execution is a separate concern.

WP-215 MAY provide:

- locale identifiers;
- catalogue descriptors;
- fallback-chain descriptions;
- deterministic catalogue discovery.

It SHALL NOT initially implement message formatting, pluralization engines or ICU abstraction.

## 11. Publication boundary

Publication maps validated source resources to deployment targets.

The architecture distinguishes:

```text
source identity
resolved source
publication target
published manifest entry
```

Publication SHALL be explicit and idempotent where possible.

The Core SHALL NOT assume a web root or URL base.

## 12. Integrity

Resource fingerprints SHOULD use SHA-256 over bytes or canonical descriptor data, depending on resource kind.

A fingerprint SHALL describe what was hashed and SHALL not be inferred from timestamps alone.

## 13. Security requirements

The subsystem SHALL:

- validate all relative paths;
- confine filesystem access to approved roots;
- reject traversal and canonical-root escape;
- avoid executing resource contents;
- avoid dynamic PHP inclusion;
- avoid trusting file extensions as proof of content type;
- keep secrets out of generated manifests;
- normalize metadata through safe scalar-or-null rules.

## 14. Compatibility boundary

WP-215 SHALL be additive.

It SHALL NOT silently replace existing application asset helpers, module behavior or public resource paths.

Migration requires explicit adapters and documented mapping.

## 15. Integration boundaries

### Logging

Implementations MAY emit structured diagnostics through WP-213 contracts.

### Error handling

Typed failures MAY be observed through WP-214 without changing the original exception identity.

### Modules

WP-212 module providers MAY contribute resource registrations through a narrow contract.

### Container

Composition MAY use Container 2.0 contracts, but the resource model SHALL remain usable without a concrete container.

## 16. Exclusions

WP-215 does not initially implement:

- CSS or JavaScript minification;
- transpilation;
- source maps;
- image conversion;
- CDN upload;
- HTTP controllers;
- browser cache headers;
- template rendering;
- runtime execution of discovered files;
- package installation;
- remote resource downloading.

## 17. Increment plan

### I1 — Architecture

Architecture, boundaries, security rules and delivery plan.

### I2 — Core value model

Identifiers, namespaces, types, paths, priorities, descriptors and typed validation failures.

### I3 — Registry and deterministic discovery

Explicit registration, duplicate policy, ordered queries and immutable snapshots.

### I4 — Filesystem roots and secure resolution

Canonical roots, traversal protection, file-backed descriptors and typed resolution outcomes.

### I5 — Module contributions and override policy

Provider contracts, ownership, priorities, explicit override decisions and diagnostics.

### I6 — Locale and translation catalogue discovery

Locale model, fallback-chain description and deterministic catalogue resolution without formatting execution.

### I7 — Publication plan and manifests

Immutable publication plan, target mapping, fingerprints, dry-run support and deterministic manifests.

### I8 — Runtime integration and completion

Service provider, application contracts, example, compatibility review, complete validation and WP closure.

## 18. Acceptance criteria

WP-215 is complete when:

- all public behavior is contract-first and documented;
- resource resolution is deterministic;
- filesystem escape is rejected by tests;
- module contributions preserve ownership and order;
- manifests are reproducible;
- no resource content is executed by the subsystem;
- PHPStan level 8 passes;
- PHPUnit passes;
- Builder validation reports zero diagnostics;
- existing public APIs remain compatible.
