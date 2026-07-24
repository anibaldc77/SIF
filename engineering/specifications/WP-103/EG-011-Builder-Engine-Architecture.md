---
id: EG-011
title: Builder Engine Architecture
summary: Defines the architecture and orchestration boundaries of the SIF Builder execution engine.
status: Draft
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-103
authors:
  - SIF Team
created: 2026-07-20
updated: 2026-07-20
tags:
  - builder
  - engine
  - architecture
depends_on: []
related_adrs: []
references: []
---

# EG-011 — Builder Engine Architecture

- Work Package: WP-103
- Status: Draft
- Version: 0.1.0
- Target release: SIF Builder 2.0.0-alpha1
- Depends on: WP-100, WP-101, WP-102

## 1. Objective

Define the architecture of the SIF Builder execution engine before implementation. The engine shall orchestrate repository discovery, indexing, analysis, generation, diagnostics, and reporting through explicit contracts, deterministic execution, and isolated extensions.

The engine is an application-level coordinator. It does not own metadata syntax, repository indexing rules, reference semantics, file-system drivers, or output formats. Those responsibilities remain in their existing subsystems and are consumed through contracts.

## 2. Problem statement

The Builder already contains reusable capabilities for metadata, repository indexing, reference parsing, reference resolution, graph construction, and file-system access. Without a governing execution engine, each capability would need to be invoked manually or coupled directly to other components.

WP-103 introduces a stable orchestration boundary that allows the Builder to:

1. execute complete repository-processing workflows;
2. register analyzers and generators without modifying the engine;
3. collect diagnostics in one normalized model;
4. preserve deterministic behavior across local, CI, and future CLI executions;
5. continue collecting safe diagnostics when an extension fails;
6. expose structured results suitable for humans, automation, and future AI-assisted tooling.

## 3. Architectural principles

1. **Orchestration, not domain duplication.** The engine coordinates existing subsystems and does not reimplement them.
2. **Contracts before implementations.** Public extension points are defined as interfaces.
3. **Deterministic execution.** Registration order, phase order, result ordering, and diagnostic ordering are stable.
4. **Immutable shared state.** Extensions receive immutable snapshots or read-only views.
5. **Explicit lifecycle.** Every run advances through governed phases.
6. **Failure isolation.** One extension failure becomes a diagnostic and does not corrupt shared state.
7. **No hidden global state.** The engine receives dependencies explicitly.
8. **No direct process termination.** Library code never calls `exit` or writes directly to the console.
9. **Safe serialization.** Throwable objects and sensitive internal values are never serialized automatically.
10. **Incremental delivery.** Each increment must be independently testable and usable.

## 4. Scope

WP-103 covers:

- engine lifecycle and phase model;
- immutable execution context;
- extension registration;
- analyzer execution;
- generator execution;
- normalized diagnostics;
- execution result and statistics;
- deterministic pipeline orchestration;
- failure policy and execution continuation rules;
- contracts for future CLI and CI adapters.

## 5. Exclusions

WP-103 does not include:

- a command-line interface;
- interactive prompts;
- repository watch mode;
- parallel execution;
- distributed execution;
- persistent caches;
- Graphviz or Mermaid rendering;
- direct Git operations;
- network access;
- AI model invocation;
- mutation of source engineering documents;
- replacement of WP-100, WP-101, or WP-102 domain rules.

## 6. Layering

```text
CLI / CI / API adapters                 (future work packages)
              |
              v
+-----------------------------------------------+
|              Builder Engine                   |
| lifecycle · registry · pipeline · diagnostics |
+-----------------------------------------------+
      |              |               |
      v              v               v
  Analyzers      Generators       Reporters
      |              |               |
      +--------------+---------------+
                     |
                     v
+-----------------------------------------------+
| Existing Builder subsystems                   |
| Metadata · Repository · Reference · FileSystem|
+-----------------------------------------------+
```

The dependency direction is downward. Existing subsystems must never depend on the Builder Engine.

## 7. Lifecycle

A run advances through the following phases:

```text
CREATED
  -> PREPARING
  -> DISCOVERING
  -> INDEXING
  -> ANALYZING
  -> GENERATING
  -> FINALIZING
  -> COMPLETED
```

A fatal engine-level failure transitions the run to:

```text
FAILED
```

Extension-level failures do not automatically fail the entire run. They produce diagnostics according to the failure policy.

### 7.1 Phase responsibilities

| Phase | Responsibility |
|---|---|
| `CREATED` | Dependencies and configuration have been accepted. |
| `PREPARING` | Validate configuration and freeze extension registration. |
| `DISCOVERING` | Discover candidate repository documents. |
| `INDEXING` | Build or receive the repository index. |
| `ANALYZING` | Execute registered analyzers in deterministic order. |
| `GENERATING` | Execute registered generators when policy permits. |
| `FINALIZING` | Sort diagnostics, calculate statistics, and assemble results. |
| `COMPLETED` | Return an immutable successful execution result. |
| `FAILED` | Return an immutable failed execution result with safe diagnostics. |

## 8. Core model

The first stable public model is expected to contain the following concepts.

### 8.1 `BuilderEngineInterface`

Primary application contract.

Responsibilities:

- accept a `BuilderRequest`;
- execute one complete run;
- return a `BuilderResult`;
- never expose partial mutable state.

Conceptual signature:

```php
public function run(BuilderRequest $request): BuilderResult;
```

### 8.2 `BuilderRequest`

Immutable input to one execution.

Minimum information:

- repository root;
- selected execution profile;
- optional output root;
- strictness policy;
- enabled analyzer identifiers;
- enabled generator identifiers.

It must not contain service objects or mutable registries.

### 8.3 `BuilderContext`

Immutable context shared with extensions.

Expected data:

- normalized repository root;
- repository index;
- metadata/index/reference services already produced by prior phases;
- execution profile;
- read-only configuration;
- current phase;
- run identifier.

A new context instance is created when phase-owned data changes. Extensions cannot mutate it.

### 8.4 `BuilderResult`

Immutable terminal result.

Expected data:

- terminal status;
- completed phases;
- diagnostics;
- generated artifacts;
- execution statistics;
- safe failure summary when applicable.

The result must be serializable without serializing throwable objects.

### 8.5 `BuilderPhase`

Enum containing the governed lifecycle values. Phase transitions must be validated.

### 8.6 `BuilderStatus`

Minimum statuses:

- `SUCCEEDED`;
- `SUCCEEDED_WITH_DIAGNOSTICS`;
- `FAILED`.

Warnings alone produce `SUCCEEDED_WITH_DIAGNOSTICS`. Errors follow the selected strictness policy.

## 9. Extension model

### 9.1 Analyzer

An analyzer reads the immutable context and emits diagnostics. It does not create repository artifacts.

```php
interface AnalyzerInterface
{
    public function id(): string;

    public function analyze(BuilderContext $context): AnalysisResult;
}
```

Analyzer identifiers are stable, lowercase, dot-separated identifiers, for example:

```text
reference.broken
reference.cycles
repository.metadata
```

### 9.2 Generator

A generator reads the immutable context and produces artifact descriptions. File writing is delegated through an approved file-system contract.

```php
interface GeneratorInterface
{
    public function id(): string;

    public function generate(BuilderContext $context): GenerationResult;
}
```

A generator must declare outputs through structured artifact objects. It must not write arbitrary files outside the approved output root.

### 9.3 Reporter

Reporter integration is deferred to a later increment of WP-103. The architecture reserves a contract that transforms `BuilderResult` into a presentation format without changing the result.

Examples include Markdown, JSON, console, and CI annotations.

### 9.4 Registry

The engine uses explicit registries for analyzers and generators.

Registry invariants:

1. identifiers are unique;
2. registration order is preserved;
3. registries become immutable when execution begins;
4. missing requested extensions produce configuration diagnostics;
5. duplicate identifiers are rejected before execution.

## 10. Pipeline

The pipeline is a deterministic sequence of stages. A stage receives a context and returns a stage result containing a new context plus diagnostics.

```text
BuilderRequest
    -> PrepareStage
    -> DiscoveryStage
    -> IndexStage
    -> AnalyzerStage
    -> GeneratorStage
    -> FinalizeStage
    -> BuilderResult
```

### 10.1 Stage contract

```php
interface BuilderStageInterface
{
    public function phase(): BuilderPhase;

    public function execute(BuilderContext $context): StageResult;
}
```

Stage implementations are engine internals in the initial release. Arbitrary third-party stage insertion is excluded until lifecycle invariants are proven stable.

### 10.2 Ordering

- stages follow the fixed lifecycle order;
- analyzers execute in registry insertion order;
- generators execute in registry insertion order;
- diagnostics are normalized and then sorted by severity, code, source, and message;
- artifacts are sorted by normalized path in the terminal result.

## 11. Diagnostics

Diagnostics are the primary communication mechanism for non-fatal problems.

### 11.1 `Diagnostic`

Minimum fields:

- stable code;
- severity;
- message;
- source identifier or path when available;
- extension identifier when applicable;
- structured safe context;
- optional remediation hint.

### 11.2 Severity

```text
INFO
WARNING
ERROR
FATAL
```

`FATAL` is reserved for failures that prevent the engine from preserving lifecycle invariants or producing a trustworthy result.

### 11.3 Diagnostic code convention

Codes use an uppercase governed prefix and numeric suffix:

```text
ENGINE-001
CONFIG-001
ANALYZER-001
GENERATOR-001
REFERENCE-001
```

Messages may evolve; codes are the stable automation contract.

### 11.4 Safety

Diagnostics must not automatically include:

- full stack traces;
- secrets;
- environment variables;
- file contents;
- connection strings;
- arbitrary throwable serialization.

The in-memory result may retain the first throwable as a non-serializable cause for debugging, following the existing Runtime Foundation pattern.

## 12. Failure policy

The request selects one of the following policies:

### `LENIENT`

- analyzer errors are collected;
- remaining analyzers continue;
- generators may run unless a fatal diagnostic exists;
- result may be `SUCCEEDED_WITH_DIAGNOSTICS`.

### `STRICT`

- analyzer errors are collected;
- remaining analyzers continue to maximize diagnostics;
- generators do not run when any `ERROR` or `FATAL` exists;
- result is `FAILED` when errors remain.

### Engine failures

The engine transitions to `FAILED` immediately when:

- phase transition is invalid;
- context integrity is lost;
- registry invariants are violated after freeze;
- a core stage cannot produce a trustworthy result.

## 13. Generated artifacts

A `GeneratedArtifact` is a description of an output, not an implicit write side effect.

Minimum fields:

- generator identifier;
- normalized relative path;
- artifact type;
- content or content provider;
- checksum after writing, when available.

Rules:

1. paths must be relative to the approved output root;
2. path traversal is rejected;
3. two generators may not claim the same output path in one run;
4. artifact ordering is deterministic;
5. writing is atomic when supported by the selected file-system driver.

## 14. Execution statistics

The terminal result should expose deterministic statistics:

- phase durations, excluding them from deterministic textual snapshots unless explicitly requested;
- discovered document count;
- indexed document count;
- analyzer count and failures;
- generator count and failures;
- diagnostic counts by severity and code prefix;
- artifact count.

Timing values are observational data and must not influence equality or deterministic output tests.

## 15. Initial integrations

WP-103 will consume existing capabilities through adapters or direct stable contracts:

| Existing subsystem | Engine use |
|---|---|
| Metadata | document metadata input and validation diagnostics |
| Repository Index | canonical indexed repository view |
| Reference Parser | reference extraction |
| Reference Resolver | broken-reference analysis |
| Reference Graph | cycle and impact analysis |
| FileSystem | controlled discovery and artifact writing |

Existing domain objects remain authoritative. The engine must not create parallel representations of repository entries or references.

## 16. Public API stability

The following elements are intended to become public extension contracts after their implementing increment is accepted:

- `BuilderEngineInterface`;
- `AnalyzerInterface`;
- `GeneratorInterface`;
- `BuilderRequest`;
- `BuilderResult`;
- `Diagnostic`;
- `GeneratedArtifact`.

Internal stages and orchestration classes remain internal until the lifecycle is validated by implementation and tests.

Breaking changes to accepted public contracts require:

1. an ADR or RFC;
2. a migration plan;
3. a versioning decision consistent with SemVer;
4. updated examples and tests.

## 17. Increment plan

### Increment 1 — Engine Core Model

- lifecycle enums;
- immutable request, context, result, and stage result;
- diagnostics and severities;
- validation exceptions;
- unit tests.

### Increment 2 — Extension Registries

- analyzer and generator contracts;
- deterministic registries;
- duplicate and missing extension diagnostics;
- registry freeze behavior;
- unit tests.

### Increment 3 — Pipeline Orchestration

- fixed internal stages;
- engine implementation;
- lifecycle transition validation;
- strict and lenient failure policies;
- integration tests with in-memory extensions.

### Increment 4 — Repository Workflow Integration

- discovery and index integration;
- reference analyzer adapters;
- immutable context enrichment;
- end-to-end repository fixtures.

### Increment 5 — Artifact Generation

- generated artifact model;
- output collision detection;
- approved output root enforcement;
- atomic writing through FileSystem;
- integration tests.

### Increment 6 — Reporting and Operational Result

- Markdown and JSON reporters;
- statistics;
- safe failure serialization;
- stable machine-readable schema;
- preparation for CLI and CI work packages.

## 18. Acceptance criteria for WP-103

WP-103 is complete when:

1. one engine request can execute the full fixed pipeline;
2. analyzers and generators can be registered without modifying engine code;
3. contexts and terminal results are immutable;
4. all diagnostics use stable codes and deterministic ordering;
5. strict and lenient policies are covered by tests;
6. extension failures cannot corrupt engine state;
7. generated paths cannot escape the configured output root;
8. the engine integrates WP-101 and WP-102 capabilities end to end;
9. PHPUnit and PHPStan level 8 pass;
10. public contracts and examples are documented.

## 19. Deferred decisions

The following require separate ADRs or later work packages:

- third-party custom stages;
- parallel analyzer execution;
- persistent incremental cache;
- process isolation for untrusted extensions;
- plugin package discovery through Composer;
- CLI exit-code mapping;
- CI annotation formats;
- AI-assisted analyzers and generators.

## 20. Implementation rule

No implementation increment may expand the scope silently. Any new lifecycle phase, public extension point, persistence mechanism, concurrency model, or external side effect requires an explicit specification update and, when architecturally significant, an ADR.
