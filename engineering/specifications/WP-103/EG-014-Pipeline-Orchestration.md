# EG-014 — Pipeline Orchestration

- Work Package: WP-103 — Builder Engine
- Increment: 3
- Status: Implemented candidate
- Version: 1.0.0
- Runtime: PHP 8.2+

## 1. Purpose

Implement the first executable Builder Engine pipeline over the core model and extension registries approved in EG-011, EG-012 and EG-013.

This increment proves deterministic lifecycle orchestration, extension execution order, failure-policy behavior and safe conversion of extension failures into diagnostics. Repository discovery and indexing remain pass-through lifecycle stages until Increment 4.

## 2. Scope

Included:

- fixed internal lifecycle stages;
- lifecycle transition validation;
- analyzer and generator orchestration;
- registry freezing at execution start;
- requested-extension selection;
- strict and lenient policy enforcement;
- safe extension failure diagnostics;
- terminal `BuilderResult` creation;
- injectable run identifier provider;
- integration tests using in-memory extensions.

Excluded:

- filesystem discovery;
- repository index creation;
- reference workflow adapters;
- generated artifact modeling or writing;
- third-party stage registration;
- CLI and reporting.

## 3. Pipeline

```text
CREATED
  -> PREPARING
  -> DISCOVERING
  -> INDEXING
  -> ANALYZING
  -> GENERATING (conditional)
  -> FINALIZING
  -> COMPLETED
```

`ANALYZING -> FINALIZING` is an approved shortcut when generation is blocked by policy.

Any non-terminal phase may transition to `FAILED` when a core lifecycle invariant cannot be preserved.

## 4. Contracts

### BuilderStageInterface

```php
interface BuilderStageInterface
{
    public function phase(): BuilderPhase;

    public function execute(BuilderContext $context): StageResult;
}
```

Stages are internal engine components in this increment.

### RunIdentifierProviderInterface

Provides a non-empty run identifier for context creation. The default implementation uses cryptographically secure random bytes. Tests inject a deterministic provider.

## 5. Extension execution

- analyzers execute in registry insertion order;
- all selected analyzers execute even when previous analyzers emitted errors;
- generators execute in registry insertion order when policy permits;
- selection diagnostics are merged before extension execution;
- registries are frozen before selection or execution.

## 6. Failure policies

### STRICT

- all analyzers execute;
- any `ERROR` or `FATAL` diagnostic blocks generators;
- the pipeline finalizes safely;
- remaining errors produce a `FAILED` result.

### LENIENT

- all analyzers execute;
- `ERROR` diagnostics do not block generators;
- `FATAL` diagnostics block generators;
- non-fatal diagnostics produce `SUCCEEDED_WITH_DIAGNOSTICS`.

## 7. Failure isolation

An analyzer throwable becomes:

```text
ANALYZER-500 / ERROR
```

A generator throwable becomes:

```text
GENERATOR-500 / ERROR
```

Throwable messages, stack traces and arbitrary internal details are not serialized into diagnostics.

A core engine throwable becomes:

```text
ENGINE-500 / FATAL
```

The first core throwable is retained only as the non-serializable `BuilderResult` cause.

## 8. Determinism

- phase order is fixed;
- extension order follows registry insertion order or explicit request order;
- diagnostic ordering is delegated to `DiagnosticCollection`;
- no timestamps are added to serialized results;
- run identifiers do not affect extension order or diagnostic ordering.

## 9. Increment 4 boundary

`DISCOVERING` and `INDEXING` are lifecycle pass-through stages in this increment. Increment 4 will replace their empty behavior with repository discovery, metadata indexing and context enrichment without changing the public pipeline contract.

## 10. Acceptance criteria

1. Approved transitions succeed and invalid transitions are rejected.
2. Registries freeze before extension execution.
3. Analyzers and generators execute deterministically.
4. Strict policy blocks generation after analyzer/configuration errors.
5. Lenient policy permits generation after non-fatal errors.
6. Fatal diagnostics block generation in every policy.
7. Missing requested extensions remain governed configuration diagnostics.
8. Extension throwables become safe diagnostics and do not escape.
9. Successful execution reaches `COMPLETED`.
10. PHPUnit and PHPStan pass for the package and complete repository.
