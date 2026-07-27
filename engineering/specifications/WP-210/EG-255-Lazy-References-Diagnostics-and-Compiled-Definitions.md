---
id: EG-255
title: Lazy References, Diagnostics and Compiled Definitions
summary: Defines explicit lazy service references, structured container diagnostics, deterministic definition validation, immutable compiled definition models, and reproducible fingerprints.
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
  - lazy
  - diagnostics
  - compilation
depends_on:
  - EG-254
  - EG-253
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-255 — Lazy References, Diagnostics and Compiled Definitions

## Purpose

This specification introduces explicit lazy service references, validation diagnostics, and a deterministic compiled container-definition model.

## Lazy references

Lazy behavior is explicit:

```php
$reference = $container->lazy($identifier);
```

The reference:

- stores the container and service identifier;
- does not resolve during construction;
- exposes `isResolved`;
- resolves once on first access;
- preserves the returned object for later calls.

This is not proxy generation.

Consumers must call `resolve()` explicitly.

## Scoped lazy references

A lazy reference created from a service scope remains bound to that scope.

The first resolution therefore preserves scoped lifetime semantics.

Using a reference after its scope has closed fails through the existing closed-scope behavior.

## Diagnostics

Container validation produces immutable diagnostics with:

- stable code;
- severity;
- message;
- scalar-or-null context.

The initial diagnostic set includes:

- missing alias target;
- alias cycle;
- missing autowired class;
- missing contextual consumer;
- missing contextual service.

Diagnostics do not resolve services or execute factories.

## Validation report

`ContainerValidationReport` exposes:

- validity;
- deterministic diagnostic list;
- diagnostic count.

Any diagnostic with severity `error` makes the report invalid.

## Compiled definitions

Compilation transforms runtime definitions into an immutable, storage-neutral representation.

The compiled model includes:

- identifier;
- definition kind;
- lifetime;
- class;
- alias target;
- autowire flag;
- explicit constructor bindings;
- tags;
- registration order.

Factory closures and instance objects are intentionally not serialized.

The compiled model is a validated description, not executable generated PHP.

## Fingerprint

Compilation computes a SHA-256 fingerprint from canonical JSON containing:

- compiled service definitions;
- contextual bindings.

The same ordered input produces the same fingerprint.

A meaningful definition change changes the fingerprint.

## Compilation failure

Compilation fails with `ContainerCompilationException` when validation reports errors.

The exception preserves the full validation report.

## Exclusions

This increment does not implement:

- generated proxy classes;
- transparent lazy injection;
- executable compiled PHP;
- cached reflection metadata;
- service preloading;
- service disposal;
- compiler passes;
- compatibility adapter;
- Framework integration.

## Acceptance criteria

- lazy references do not resolve eagerly;
- lazy references resolve once;
- scoped lazy references preserve scope identity;
- validation never creates services;
- diagnostics use stable codes;
- diagnostics are deterministically ordered;
- invalid definitions cannot compile;
- compiled models are immutable;
- fingerprints are reproducible;
- definition changes alter fingerprints;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
