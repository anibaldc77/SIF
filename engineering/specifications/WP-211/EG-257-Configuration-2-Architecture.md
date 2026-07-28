---
id: EG-257
title: Configuration 2.0 Architecture
summary: Defines the compatible evolution of SIF configuration into a source-neutral, layered, provenance-aware, schema-capable, secret-safe, observable, and snapshot-oriented subsystem.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-211
tags:
  - foundation
  - configuration
  - architecture
  - provenance
  - secrets
  - schemas
depends_on:
  - EG-207
  - EG-208
  - EG-209
  - EG-256
  - EG-212
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-257 — Configuration 2.0 Architecture

## 1. Purpose

WP-211 defines the compatible evolution of the SIF configuration subsystem.

Configuration 2.0 SHALL provide a source-neutral, deterministic, observable, secret-safe, and extensible configuration foundation while preserving the contracts and behavior already delivered by WP-203.

WP-211-I1 is exclusively architectural. It introduces no production PHP code.

## 2. Existing baseline

WP-203 already provides:

- `ConfigurationInterface`;
- `MutableConfigurationInterface`;
- `ConfigurationRepository`;
- dot-notation access;
- explicit required-value resolution;
- controlled mutation;
- irreversible freeze semantics;
- PHP configuration loading;
- JSON configuration loading;
- deterministic multi-source precedence;
- structural merge behavior;
- application ownership;
- Runtime bootstrap integration;
- provider-time mutation;
- freeze after successful boot;
- capability exposure.

WP-211 SHALL treat this behavior as a compatibility baseline, not as disposable prototype code.

## 3. Architectural goals

Configuration 2.0 SHALL support:

1. source-neutral configuration providers;
2. deterministic layered composition;
3. value provenance;
4. typed retrieval and conversion;
5. schema validation;
6. secret classification and redaction;
7. immutable snapshots;
8. structured diagnostics;
9. optional caching and reproducibility;
10. controlled integration with Container 2.0 and Runtime;
11. migration from WP-203 without breaking existing consumers.

## 4. Dependency direction

The intended dependency direction is:

```text
Application / Module / Runtime
            |
            v
Configuration read contracts
            |
            v
Configuration repository and snapshots
            |
            v
Composition, validation, and diagnostics
            |
            v
Source providers and format adapters
```

Source providers SHALL NOT depend on Runtime, Application, or concrete modules.

The configuration repository SHALL NOT perform filesystem, network, environment, or secret-store access directly.

## 5. Contract layers

Configuration 2.0 SHOULD distinguish the following contract layers.

### 5.1 Read contract

The existing `ConfigurationInterface` remains the minimum read contract.

It SHALL preserve:

- `has`;
- `get`;
- `require`;
- `all`;
- `isFrozen`.

### 5.2 Typed read contract

A new typed access contract MAY expose explicit conversion methods such as:

- string;
- integer;
- float;
- boolean;
- list;
- map;
- enum;
- nullable variants.

Typed access SHALL fail explicitly when conversion is ambiguous or unsafe.

Implicit locale-dependent conversion SHALL NOT occur.

### 5.3 Mutation contract

`MutableConfigurationInterface` remains valid for controlled composition and provider boot phases.

Mutation SHALL remain unavailable to ordinary consumers unless explicitly required.

### 5.4 Snapshot contract

An immutable snapshot SHALL represent one complete, validated configuration state.

A snapshot SHOULD expose:

- values;
- fingerprint;
- creation metadata;
- source summary;
- validation status;
- safe diagnostics.

## 6. Configuration keys

The existing dot-notation model remains supported.

A future `ConfigurationKey` value object SHOULD normalize and validate keys before repository access.

Rules SHALL include:

- non-empty keys;
- no empty path segments;
- case-sensitive comparison;
- explicit distinction between absent and defined `null`;
- no implicit wildcard expansion in the Core.

Existing string-key APIs SHALL remain available through compatibility contracts.

## 7. Values and normalization

The Core configuration value model SHOULD support values representable as deterministic data:

- `null`;
- booleans;
- integers;
- finite floats;
- strings;
- lists of configuration values;
- maps with non-empty string keys.

Objects, resources, closures, and executable values SHALL NOT be part of canonical snapshots.

PHP source loaders MAY receive arbitrary PHP return values initially, but canonical normalization SHALL reject unsupported values before snapshot creation.

## 8. Source providers

A configuration source provider SHALL produce a source result without mutating the repository.

A provider SHOULD expose:

- source identifier;
- source type;
- precedence;
- optional profile or environment constraints;
- loaded values;
- provenance metadata;
- diagnostics.

Initial provider families MAY include:

- arrays;
- PHP files;
- JSON files;
- environment repository projection;
- command-line overrides;
- in-memory test sources.

YAML, INI, remote stores, vaults, and database-backed providers SHALL remain optional adapters, not Core dependencies.

## 9. Layered composition

Composition SHALL be explicit and deterministic.

Sources SHALL be ordered by:

1. declared precedence;
2. stable registration order for equal precedence.

Later or higher-precedence values SHALL override earlier values according to a declared merge policy.

The existing WP-203 semantics remain the default compatibility policy:

- associative maps merge recursively;
- lists replace as complete values;
- scalar/array conflicts are resolved by the later source;
- an empty incoming array replaces the earlier value.

Alternative merge policies MAY be introduced only as explicit strategies.

## 10. Provenance

Configuration 2.0 SHALL be able to identify where a resolved value originated.

Provenance SHOULD include safe metadata such as:

- source identifier;
- source type;
- precedence;
- original key;
- composition sequence;
- whether the value was overridden;
- whether the value is classified as secret.

Provenance SHALL NOT expose secret values.

The repository MAY store provenance separately from values to preserve a simple read surface.

## 11. Schema and validation

Validation SHALL be explicit and composable.

A configuration schema SHOULD support:

- required keys;
- expected value types;
- nullable rules;
- allowed values;
- numeric ranges;
- string constraints;
- list item constraints;
- map shape constraints;
- deprecation metadata;
- secret classification;
- default values where explicitly approved.

Schema validation SHALL NOT execute arbitrary user code.

Validation SHOULD occur before freeze and before creation of a production snapshot.

## 12. Secrets and redaction

Secret safety is mandatory.

A secret classification policy MAY classify values through:

- schema metadata;
- explicit key registration;
- provider metadata;
- conservative key-pattern rules.

Diagnostics, snapshots intended for display, logs, audit records, exceptions, and Builder artifacts SHALL redact secret values.

Redaction SHALL preserve enough structural information for diagnosis without revealing the original value.

The configuration subsystem SHALL NOT become a secret manager. It MAY consume values from secret-provider adapters.

## 13. Diagnostics

Configuration failures SHALL be represented by stable codes and structured context.

Diagnostic categories SHOULD include:

- source access;
- source decoding;
- unsupported values;
- merge conflicts;
- invalid keys;
- type conversion failures;
- schema violations;
- missing required values;
- secret-policy violations;
- cache corruption;
- fingerprint mismatch.

Diagnostic context SHALL be scalar-or-null and secret-safe.

## 14. Snapshots and fingerprints

A validated configuration snapshot SHALL be immutable.

A canonical fingerprint SHOULD be derived from normalized values and relevant composition metadata.

Fingerprint generation SHALL be:

- deterministic;
- independent of object identity;
- independent of filesystem traversal order;
- stable for semantically equivalent normalized input;
- secret-safe when displayed.

A fingerprint identifies a configuration state; it does not encrypt or protect secrets.

## 15. Cache architecture

Configuration caching SHALL be optional.

A cache adapter MAY persist a canonical snapshot representation.

Cache validity SHOULD consider:

- schema version;
- subsystem version;
- source descriptors;
- source freshness metadata where available;
- snapshot fingerprint;
- normalization format version.

The Core SHALL not require a filesystem cache.

Cache failures SHALL fall back only according to an explicit policy. Silent use of stale or corrupt data is prohibited.

## 16. Runtime integration

The current WP-203 lifecycle behavior remains valid:

- configuration is mutable during provider registration and boot;
- configuration freezes after successful boot;
- failed boot does not freeze configuration.

A future WP-211 integration increment SHOULD replace the mutable repository contents with a validated snapshot at the same successful lifecycle boundary.

Runtime integration SHALL remain additive and SHALL preserve `ApplicationInterface` compatibility.

## 17. Container 2.0 integration

Configuration MAY be exposed to Container 2.0 through explicit definitions and constructor bindings.

The container SHALL NOT read arbitrary configuration keys implicitly during autowiring.

Approved integration patterns MAY include:

- binding `ConfigurationInterface` to the application repository;
- explicit parameter-value providers;
- schema-backed configuration objects;
- module-owned configuration factories.

Configuration lookup by parameter name alone SHALL NOT be introduced as hidden Core behavior.

## 18. Module integration

Modules MAY declare configuration contributions and schemas through explicit contracts.

Module configuration discovery SHALL be deterministic and SHALL NOT depend on unrestricted filesystem scanning.

A module SHALL NOT mutate another module's configuration namespace without an explicit extension contract.

Namespace ownership and conflict policy SHALL be specified before implementation.

## 19. Reload model

Dynamic reload is not part of the initial Core implementation.

If introduced later, reload SHALL create a new immutable snapshot rather than mutating a live frozen snapshot.

Snapshot replacement SHALL require an explicit owner and observable lifecycle event.

Consumers requiring stable configuration SHALL be able to retain the prior snapshot.

## 20. Security boundaries

Configuration sources are data boundaries.

The subsystem SHALL:

- reject unsupported executable values in canonical snapshots;
- avoid logging raw source contents;
- avoid exposing secrets through exceptions;
- constrain file-source access to explicitly supplied paths;
- preserve original causes without serializing unsafe state;
- avoid implicit network access;
- avoid global mutable configuration.

PHP file loaders remain trusted-code adapters and SHALL be documented as such.

## 21. Compatibility strategy

WP-211 SHALL preserve existing WP-203 behavior through staged evolution.

The compatibility strategy is:

1. retain current interfaces;
2. introduce new value objects and contracts additively;
3. adapt existing repository and loaders;
4. preserve current merge semantics as the default policy;
5. preserve Runtime freeze timing;
6. add snapshots, provenance, schemas, and diagnostics behind new contracts;
7. provide migration documentation before deprecating any API;
8. avoid changing `Framework::create()` without a dedicated integration increment.

EG-207, EG-208, and EG-209 are not superseded by this architecture document.

They remain the behavioral baseline until later WP-211 increments explicitly map and migrate each contract.

## 22. Proposed increment plan

### WP-211-I1 — Architecture

- architectural boundaries;
- compatibility model;
- source, provenance, schema, secret, snapshot, cache, Runtime, Container, and module decisions.

### WP-211-I2 — Core value model and read contracts

- configuration key;
- normalized value rules;
- typed access contract;
- immutable source descriptor;
- core exceptions;
- compatibility tests.

### WP-211-I3 — Source providers and composition pipeline

- provider contracts;
- array provider;
- adapters for current PHP and JSON loaders;
- deterministic precedence;
- merge policies;
- provenance capture.

### WP-211-I4 — Schema and validation

- schema model;
- constraints;
- defaults;
- validation diagnostics;
- deprecation metadata.

### WP-211-I5 — Secrets, redaction, and safe diagnostics

- classification policy;
- redacted views;
- diagnostic taxonomy;
- safe exception context.

### WP-211-I6 — Snapshots, canonical serialization, and fingerprints

- immutable snapshots;
- canonical serializer;
- reproducible fingerprint;
- comparison and change summary.

### WP-211-I7 — Cache and composition integration

- optional cache contracts;
- cache validation;
- Container 2.0 composition;
- migration adapter for WP-203 repository and loaders.

### WP-211-I8 — Runtime reference integration and product completion

- controlled Runtime integration;
- lifecycle validation and freeze;
- vertical example;
- migration guide;
- complete quality gate;
- product closure.

## 23. Deferred scope

The following are deferred unless separately approved:

- YAML dependency in Core;
- remote HTTP configuration;
- cloud-provider SDKs;
- secret-manager implementation;
- live filesystem watching;
- hot reload;
- distributed configuration consensus;
- encrypted-at-rest cache implementation;
- configuration UI;
- implicit parameter-name injection;
- global static configuration;
- automatic module filesystem discovery.

## 24. Acceptance criteria

WP-211-I1 is accepted when:

1. WP-203 behavior is explicitly preserved as the compatibility baseline.
2. source providers are separated from repository mutation.
3. composition and precedence are deterministic.
4. provenance is defined without secret leakage.
5. schema and validation boundaries are defined.
6. secret classification and redaction are mandatory.
7. immutable snapshots and fingerprints are defined.
8. cache behavior is optional and explicit.
9. Runtime and Container integration remain controlled.
10. module ownership boundaries are defined.
11. deferred scope is explicit.
12. the incremental implementation plan is approved.
13. no production code changes are introduced.
14. SIF Builder reports zero diagnostics.
