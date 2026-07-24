---
id: EG-013
title: Extension Registries
summary: Introduce the governed analyzer and generator extension points required by the Builder Engine, together with deterministic registries, explicit selection results, missing-extension diagnostics, duplicate rejection, and registry freeze behav.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-20
updated: 2026-07-22
tags:
  - extension
  - registries
work_package: WP-103
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-013 — Extension Registries

- Work Package: WP-103
- Increment: 2
- Status: Draft
- Version: 0.1.0
- Target release: SIF Builder 2.0.0-alpha1
- Depends on: EG-011, EG-012

## 1. Objective

Introduce the governed analyzer and generator extension points required by the Builder Engine, together with deterministic registries, explicit selection results, missing-extension diagnostics, duplicate rejection, and registry freeze behavior.

## 2. Scope

This increment includes:

- `AnalyzerInterface` and `GeneratorInterface`;
- minimal `AnalysisResult` and `GenerationResult` diagnostic results;
- analyzer and generator registries;
- deterministic insertion-order lookup and selection;
- registry freezing;
- duplicate and invalid identifier exceptions;
- missing requested extension diagnostics;
- unit tests.

## 3. Exclusions

The following remain deferred:

- execution of analyzers and generators;
- lifecycle orchestration;
- throwable isolation and conversion to diagnostics;
- generated artifact descriptions;
- repository workflow integration;
- reporters and statistics;
- arbitrary third-party pipeline stages.

## 4. Extension contracts

Analyzers consume an immutable `BuilderContext` and return diagnostics through `AnalysisResult`.

```php
interface AnalyzerInterface
{
    public function id(): string;

    public function analyze(BuilderContext $context): AnalysisResult;
}
```

Generators consume the same immutable context and return `GenerationResult`.

```php
interface GeneratorInterface
{
    public function id(): string;

    public function generate(BuilderContext $context): GenerationResult;
}
```

Artifact descriptions are intentionally absent until WP-103 Increment 5. The result objects introduced here establish a stable diagnostic boundary without prematurely defining artifact semantics.

## 5. Identifier governance

Extension identifiers:

1. are normalized to lowercase;
2. contain one or more alphanumeric segments;
3. use a dot as the only segment separator;
4. reject empty segments, spaces, hyphens, underscores, and leading or trailing dots.

Examples:

```text
reference.broken
reference.cycles
repository.index
```

The registry uses the normalized identifier as its canonical key.

## 6. Registry invariants

Each registry:

1. preserves insertion order;
2. rejects duplicate normalized identifiers;
3. supports deterministic `has`, `get`, and `all` operations;
4. selects all extensions when no identifiers are requested;
5. preserves request order when an explicit selection is supplied;
6. emits diagnostics for requested but missing extensions;
7. becomes immutable after `freeze()`;
8. keeps read and selection operations available after freezing;
9. treats repeated `freeze()` calls as idempotent.

## 7. Error model

Structural violations throw exceptions before execution:

- `InvalidExtensionIdentifierException`;
- `DuplicateExtensionException`;
- `ExtensionRegistryFrozenException`.

Operational configuration problems are returned as diagnostics:

| Code | Meaning |
|---|---|
| `CONFIG-101` | Requested analyzer is not registered. |
| `CONFIG-102` | Requested generator is not registered. |

The future pipeline decides whether these diagnostics fail or continue execution according to `ExecutionPolicy`.

## 8. Selection model

`AnalyzerSelection` and `GeneratorSelection` contain:

- the ordered list of available selected extensions;
- a `DiagnosticCollection` describing missing requested extensions.

Selection does not execute extensions and does not mutate the registry.

## 9. Determinism

Determinism is guaranteed by:

- canonical normalized identifiers;
- insertion-order storage;
- explicit request-order selection;
- deterministic `DiagnosticCollection` ordering;
- absence of environment-dependent values.

## 10. Acceptance criteria

The increment is accepted when:

1. valid extension identifiers normalize deterministically;
2. invalid identifiers are rejected;
3. duplicate registration is rejected after normalization;
4. insertion order is preserved;
5. requested selection preserves request order;
6. missing requested extensions produce stable diagnostics;
7. empty requested selections return all registered extensions;
8. frozen registries reject registration and remain readable;
9. analyzer and generator results expose diagnostic success;
10. PHPUnit passes;
11. PHPStan level 8 passes;
12. the complete existing suite remains green.

## 11. Next increment

WP-103 Increment 3 will introduce:

- fixed lifecycle stages;
- phase-transition validation;
- the concrete Builder Engine;
- analyzer and generator execution;
- strict and lenient policy behavior;
- extension failure isolation;
- integration tests using in-memory extensions.
