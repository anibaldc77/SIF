# EG-022 — Engine Composition and Request Mapping

- **Work Package:** WP-104
- **Increment:** 4 of 6
- **Status:** Implemented
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** EG-018, EG-019, EG-020, EG-021, WP-103

## 1. Purpose

Connect the CLI command layer to the WP-103 Builder Engine through explicit factories, deterministic path resolution, and validated request mapping.

## 2. Components

The increment introduces:

- `BuilderEngineFactoryInterface`;
- `BuilderRequestFactoryInterface`;
- `PathResolverInterface`;
- `WorkingDirectoryPathResolver`;
- `BuilderRequestFactory`;
- `EngineExecutionMode`;
- `DefaultBuilderEngineFactory`;
- real `BuildCommand` and `ValidateCommand` implementations.

## 3. Execution modes

`BUILD` composes the configured generator registry and artifact writer.

`ANALYSIS_ONLY` composes an empty generator registry and no artifact writer. This is required because WP-103 defines an empty requested-generator list as “select all registered generators”; therefore validation cannot be represented safely only through `BuilderRequest`.

`validate` always uses `ANALYSIS_ONLY`. `build --no-write` also uses `ANALYSIS_ONLY`.

## 4. Request mapping

The request factory maps:

- one positional repository path or `--repository`;
- `--output`;
- `--policy`, `--strict`, or `--lenient`;
- repeated `--analyzer`;
- repeated `--generator` for build mode.

Relative paths resolve against an injected absolute working directory. The resolver performs lexical normalization only and does not access or mutate the filesystem.

## 5. Command behavior

Commands validate and map input before creating or invoking an Engine. Mapping errors return `INVALID_USAGE` and never invoke the Engine.

At this increment commands retain the raw `BuilderResult` in a successful `CommandResult`. Rendering and final outcome-to-exit-code mapping are explicitly deferred to Increment 5.

## 6. Composition root

`DefaultBuilderEngineFactory` centralizes construction of `BuilderEngine` from preconfigured registries, stages, run identifiers, lifecycle, and artifact writer. Commands never instantiate the Engine graph.

Concrete registration of project-specific analyzers and generators remains an application bootstrap responsibility and will be finalized with executable packaging.

## 7. Compatibility

No WP-103 public contract is modified. No new Composer namespace is required beyond the existing `Sif\\Builder\\Cli\\` mapping.

## 8. Acceptance criteria

- request mapping is deterministic;
- paths resolve relative to an injected working directory;
- invalid mapping does not invoke the Engine;
- build invokes build composition;
- validate and `--no-write` invoke analysis-only composition;
- commands preserve the `BuilderResult` for Increment 5;
- PHPUnit and PHPStan level 8 pass.
