---
id: EG-018
title: Builder CLI Architecture
summary: WP-104 defines the command-line adapter for the SIF Builder Engine. The CLI shall expose the capabilities completed in WP-103 without moving orchestration, repository analysis, generation, reporting, or policy decisions out of the Engine.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - builder
  - architecture
work_package: WP-104
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-018 — Builder CLI Architecture

- **Work Package:** WP-104
- **Status:** Proposed for implementation
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** WP-100, WP-101, WP-102, WP-103
- **Target:** SIF Builder v2.0.0-alpha1

## 1. Purpose

WP-104 defines the command-line adapter for the SIF Builder Engine. The CLI shall expose the capabilities completed in WP-103 without moving orchestration, repository analysis, generation, reporting, or policy decisions out of the Engine.

The CLI is an adapter. It translates terminal input into a `BuilderRequest`, composes the required Engine services, executes the Engine once, renders the resulting `BuilderResult`, and maps the terminal outcome to a stable process exit code.

## 2. Objectives

The CLI architecture shall:

1. provide a stable executable entry point for local and CI use;
2. preserve the deterministic behavior of the Engine;
3. avoid duplicating Engine lifecycle or business rules;
4. support human-readable and machine-readable output;
5. map outcomes to documented exit codes;
6. validate all user input before invoking the Engine;
7. remain independent from any specific third-party console framework;
8. be testable without spawning operating-system processes;
9. avoid leaking internal exception details by default;
10. remain portable across supported PHP and operating systems.

## 3. Non-goals

WP-104 does not introduce:

- an interactive shell;
- background workers or daemons;
- parallel execution;
- remote execution;
- plugin discovery through Composer;
- persistent incremental caches;
- CI-provider-specific annotations;
- automatic modification of source metadata;
- AI-assisted commands;
- a general-purpose console framework for SIF applications.

These concerns require later work packages or explicit ADRs.

## 4. Architectural position

```text
Operating System / Terminal / CI
                |
                v
        CLI Entry Point
                |
                v
      Application / Command
                |
                v
     Request Factory + Validation
                |
                v
       Engine Composition Root
                |
                v
         BuilderEngine (WP-103)
                |
                v
          BuilderResult
                |
                v
   Reporter + Output + Exit Mapper
```

Dependency direction:

```text
CLI -> Engine contracts and implementations
Engine -X-> CLI
```

No class under `Sif\Builder\Engine` may depend on a CLI namespace.

## 5. Namespace and filesystem layout

The implementation shall use:

```text
tools/builder/src/Cli/
    Application/
    Command/
    Configuration/
    Contract/
    Exception/
    Input/
    Output/
    Runtime/
```

Tests shall use:

```text
tools/builder/tests/Cli/
```

Composer mapping:

```json
"Sif\\Builder\\Cli\\": "tools/builder/src/Cli/"
```

Test mapping:

```json
"Sif\\Builder\\Tests\\Cli\\": "tools/builder/tests/Cli/"
```

## 6. Executable entry point

The public executable shall be:

```text
bin/sif-builder
```

A Windows-compatible PHP entry point may additionally be provided:

```text
bin/sif-builder.php
```

The executable shall contain only bootstrap responsibilities:

1. load Composer autoload;
2. create the CLI application through the composition root;
3. pass raw arguments and environment data;
4. write the returned output;
5. terminate using the returned exit code.

No repository scanning, Engine lifecycle logic, or generation logic may be implemented in the executable script.

## 7. Command model

### 7.1 Initial commands

WP-104 shall provide these public commands:

```text
sif-builder build
sif-builder validate
sif-builder list
sif-builder help
sif-builder version
```

### 7.2 `build`

Runs the complete Builder Engine pipeline, including analyzers and generators selected by the request.

Conceptual syntax:

```text
sif-builder build [repository-root] [options]
```

### 7.3 `validate`

Runs repository discovery, indexing, reference processing, and analyzers without persisting generated artifacts.

The implementation shall express this through Engine configuration and request selection rather than a duplicated pipeline.

### 7.4 `list`

Lists registered analyzers, generators, and reporters using deterministic ordering.

### 7.5 `help`

Displays global or command-specific usage information.

### 7.6 `version`

Displays the Builder version through a version-provider contract. Version text must not be duplicated across executable scripts and application classes.

## 8. Command dispatch

The application shall use explicit command objects registered in a deterministic `CommandRegistry`.

Minimum contract:

```php
interface CommandInterface
{
    public function name(): string;

    public function description(): string;

    public function execute(CommandInput $input): CommandResult;
}
```

The registry shall:

- normalize command names;
- reject duplicates;
- preserve insertion order for help output;
- expose exact lookup;
- be frozen before execution;
- reject mutation after freezing.

Command aliases are deferred unless explicitly specified in an implementation increment.

## 9. Input model

Raw terminal arguments shall first be captured in an immutable `ArgvInput` and then parsed into an immutable `CommandInput`.

The parser shall distinguish:

- command name;
- positional arguments;
- long options such as `--format=json`;
- boolean flags such as `--strict`;
- repeated options such as `--analyzer=id`;
- the `--` end-of-options separator.

The initial architecture does not require short-option clustering such as `-qvf`.

### 9.1 Common options

The following options are governed:

```text
--repository=<path>
--output=<path>
--policy=<strict|lenient>
--analyzer=<identifier>      repeatable
--generator=<identifier>     repeatable
--format=<markdown|json>
--no-write
--quiet
--verbose
--help
--version
```

Command-specific specifications may reduce or extend the accepted set without changing the parser contract.

### 9.2 Path semantics

- Repository and output paths shall be resolved by a dedicated path resolver.
- Relative paths shall resolve against an injected working directory.
- The parser shall not access the filesystem.
- Filesystem validation belongs to command validation or Engine preparation.
- Input objects shall preserve normalized paths without silently creating directories.

## 10. Request construction

A `BuilderRequestFactory` shall translate validated `CommandInput` into the WP-103 `BuilderRequest`.

It shall map:

- repository root;
- output root;
- strict or lenient execution policy;
- analyzer identifiers;
- generator identifiers;
- write behavior.

Reporter selection is a CLI presentation concern and shall not be added to `BuilderRequest` unless the Engine itself later requires it.

The factory shall not execute the Engine and shall not write output.

## 11. Engine composition root

The CLI shall create the Engine through a dedicated composition contract:

```php
interface BuilderEngineFactoryInterface
{
    public function create(): BuilderEngineInterface;
}
```

The default composition root shall assemble:

- repository scanner;
- metadata parser and registry support;
- repository index builder;
- reference parser and resolver;
- analyzer registry;
- generator registry;
- artifact writer;
- lifecycle stages;
- run identifier provider.

Composition shall be centralized. Commands must not instantiate the Engine dependency graph directly.

## 12. Output model

CLI output shall be represented independently from `echo`, global streams, or terminal detection.

Minimum contract:

```php
interface OutputInterface
{
    public function write(string $content): void;

    public function writeError(string $content): void;
}
```

Production adapters may target standard output and standard error. Tests shall use an in-memory implementation.

### 12.1 Stream policy

- requested report content goes to standard output;
- input and configuration errors go to standard error;
- unexpected operational failures go to standard error;
- help and version go to standard output;
- machine-readable JSON must not be mixed with progress text;
- quiet mode suppresses non-essential human output, not fatal errors.

### 12.2 Formatting

The CLI shall reuse WP-103 reporters:

- `report.markdown` for human-readable output;
- `report.json` for machine-readable output.

A `ReporterRegistry` may be introduced in WP-104, but reporters themselves remain Engine-level projections and shall not depend on terminal APIs.

## 13. Command result

Command execution shall return an immutable `CommandResult` containing:

- `ExitCode`;
- optional standard-output payload;
- optional standard-error payload;
- optional `BuilderResult` for programmatic adapters;
- safe failure summary when applicable.

A command shall not call `exit()`.

Only the executable entry point may terminate the process.

## 14. Exit-code contract

The CLI shall define a stable enum or value object. Initial mapping:

```text
0   SUCCESS
2   INVALID_USAGE
3   CONFIGURATION_ERROR
4   VALIDATION_FAILED
5   GENERATION_FAILED
6   PARTIAL_SUCCESS
10  INTERNAL_ERROR
```

### 14.1 Mapping rules

- `SUCCESS`: command completed and Builder status is successful.
- `INVALID_USAGE`: unknown command, invalid option, missing required argument, or malformed value.
- `CONFIGURATION_ERROR`: unavailable requested analyzer, generator, reporter, invalid path configuration, or composition problem attributable to configuration.
- `VALIDATION_FAILED`: validation command completed with error or fatal diagnostics.
- `GENERATION_FAILED`: build command failed to produce or persist the required artifacts.
- `PARTIAL_SUCCESS`: lenient execution completed with reportable errors while producing a usable result.
- `INTERNAL_ERROR`: unexpected exception or invariant violation.

Exit-code mapping shall be implemented by a dedicated mapper. Commands shall not scatter integer literals.

## 15. Error model

The CLI shall distinguish:

1. **Usage errors** — invalid user syntax;
2. **Configuration errors** — valid syntax referring to unavailable or invalid configuration;
3. **Builder outcomes** — represented by `BuilderResult` and diagnostics;
4. **Internal failures** — unexpected exceptions.

Expected user errors shall use dedicated exceptions or validation results and shall not include stack traces in normal output.

A debug mode may expose additional details in a future increment, but it must never modify JSON report schemas silently.

## 16. Determinism requirements

For identical input and repository state:

- command parsing must be deterministic;
- extension selection order must remain deterministic;
- help output must preserve command registration order;
- list output must preserve registry order;
- JSON output must preserve the WP-103 schema;
- Markdown output must preserve stable section and row ordering;
- exit-code mapping must be stable.

Terminal width, color support, locale, and current time shall not alter machine-readable output.

## 17. Security and safety

The CLI shall:

- reject output paths escaping approved roots through the existing artifact model;
- avoid shell interpolation and command execution;
- never evaluate repository metadata as PHP code;
- never reveal internal throwable traces by default;
- avoid including environment secrets in diagnostics;
- treat terminal escape sequences in repository-controlled values as untrusted text;
- avoid overwriting source files unless a later command explicitly governs that behavior.

## 18. Testing strategy

Tests shall be layered:

### 18.1 Unit tests

- argument parsing;
- option validation;
- command registry behavior;
- request factory mapping;
- exit-code mapping;
- output buffering;
- help and version rendering.

### 18.2 Application tests

Run the CLI application in-process using:

- synthetic argv arrays;
- in-memory output;
- fake Engine factory;
- deterministic Builder results.

### 18.3 Integration tests

- compose the real Engine with in-memory or temporary repository fixtures;
- execute `validate` and `build` without spawning a subprocess;
- verify report selection and artifact behavior.

### 18.4 Smoke tests

A minimal executable smoke test may spawn PHP to verify the real entry point, but subprocess tests must remain a small outer layer.

All implementation increments must pass:

```text
Composer validation
PHPUnit
PHPStan level 8
git diff --check
```

## 19. Public API stability

The following shall be treated as public after introduction:

- executable command names;
- documented option names and value semantics;
- exit codes;
- machine-readable output schemas;
- command and application contracts explicitly marked public.

Breaking changes require:

- specification update;
- migration guidance;
- versioning decision;
- deprecation period when feasible.

## 20. Proposed implementation increments

### Increment 1 — CLI Core Model

- `ExitCode`;
- `CommandInput`;
- `CommandResult`;
- command and output contracts;
- CLI exceptions;
- tests for immutability and validation.

### Increment 2 — Argument Parsing and Command Registry

- `ArgvInput`;
- deterministic parser;
- option definitions;
- command registry and freeze behavior;
- usage diagnostics;
- unit tests.

### Increment 3 — Application and Core Commands

- CLI application;
- help and version commands;
- build and validate command shells;
- list command;
- in-memory application tests.

### Increment 4 — Engine Composition and Request Mapping

- Engine factory contract;
- default composition root;
- Builder request factory;
- repository and output path resolution;
- real `build` and `validate` integration.

### Increment 5 — Reporting and Exit Mapping

- reporter registry or selector;
- Markdown and JSON output selection;
- exit-code mapper;
- stdout/stderr policy;
- strict, lenient, partial, and failure scenarios.

### Increment 6 — Executable Packaging and End-to-End Validation

- `bin/sif-builder`;
- Windows-compatible entry point where required;
- executable smoke tests;
- usage documentation;
- CI-ready invocation examples;
- WP-104 completion report.

## 21. Acceptance criteria

WP-104 is complete when:

1. `build`, `validate`, `list`, `help`, and `version` are executable through one application entry point;
2. commands invoke WP-103 through contracts rather than duplicating its pipeline;
3. invalid syntax never invokes the Engine;
4. Markdown and JSON reports are selectable and cleanly separated from errors;
5. exit codes follow the documented mapping;
6. build and validate are covered by in-process integration tests;
7. the real executable has a smoke test;
8. no CLI class is referenced by Engine code;
9. public commands, options, schemas, and exit codes are documented;
10. Composer, PHPUnit, PHPStan level 8, and whitespace validation pass.

## 22. Deferred decisions

Separate work packages or ADRs are required for:

- third-party command registration;
- shell completion scripts;
- colored terminal output;
- interactive prompts;
- CI-provider annotations;
- Composer plugin discovery;
- process isolation;
- remote execution;
- persistent configuration files;
- incremental cache controls;
- AI-specific CLI commands.

## 23. Implementation rule

No implementation increment may silently add commands, change an exit code, broaden filesystem side effects, duplicate Engine policy, or alter the JSON report schema. Such changes require an explicit specification update and, when architecturally significant, an ADR.
