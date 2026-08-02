---
id: EG-337
title: Developer CLI Architecture and Command Model
summary: Defines the governed architecture, command model, input-output boundary, operational safety rules and eight-increment delivery roadmap for the SIF Developer CLI.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-01
updated: 2026-08-01
work_package: WP-221
tags:
  - foundation
  - cli
  - console
  - commands
  - operations
  - installer
  - migrations
  - modules
  - architecture
depends_on:
  - EG-213
  - EG-226
  - EG-241
  - EG-248
  - EG-297
  - EG-304
  - EG-305
  - EG-312
  - EG-320
  - EG-328
  - EG-336
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-337 — Developer CLI Architecture and Command Model

## 1. Purpose

WP-221 establishes the SIF Developer CLI as the governed operational interface for developers, maintainers and deployment automation.

The CLI SHALL expose explicit commands over existing SIF subsystems without moving business logic, installation rules, migration semantics, module lifecycle or runtime diagnostics into console-specific classes.

The CLI SHALL remain an adapter at the system boundary:

```text
Terminal / process invocation
          ↓
SIF Developer CLI
          ↓
Application services and subsystem contracts
```

The console layer SHALL parse input, select commands, coordinate execution, render results and return deterministic process exit codes. Existing subsystems SHALL remain authoritative for their domain behavior.

## 2. Architectural objectives

WP-221 SHALL provide:

1. an immutable command identity and metadata model;
2. a command contract independent of global `argv` and direct terminal access;
3. deterministic command registration and resolution;
4. structured input arguments and named options;
5. explicit output, error and verbosity channels;
6. stable exit-code semantics;
7. a console kernel responsible for one command execution boundary;
8. discoverable help and command listing;
9. configuration and environment diagnostics;
10. migration inspection and execution commands;
11. Installer planning, dry-run and execution commands;
12. module and resource inspection commands where supported by existing contracts;
13. maintenance and cache-oriented commands through explicit services;
14. extension through Service Providers without command discovery by unrestricted reflection;
15. non-interactive operation suitable for CI and deployment automation;
16. runtime integration without executing commands during application boot.

## 3. Dependency direction

The mandatory dependency direction is:

```text
CLI command adapters
        ↓
CLI application services
        ↓
Existing SIF subsystem contracts
```

The following dependencies are permitted:

```text
CLI → Container contracts
CLI → Configuration contracts
CLI → Runtime and Environment contracts
CLI → Installer contracts
CLI → Migration contracts
CLI → Module contracts
CLI → Resource contracts
CLI → Logging and Error contracts
```

The inverse dependencies are prohibited:

```text
Installer  -X→ CLI
Migration  -X→ CLI
Modules    -X→ CLI
Persistence-X→ CLI
BaseModel  -X→ CLI
```

No Foundation subsystem SHALL depend on terminal formatting, ANSI support, process arguments or console input classes.

## 4. Command model

A command SHALL be represented by immutable metadata and an executable handler.

Minimum metadata:

- canonical command name;
- short description;
- optional long help text;
- ordered argument definitions;
- ordered option definitions;
- operational classification;
- interaction policy;
- destructive-operation marker;
- aliases, when explicitly declared.

A canonical command name SHALL use lowercase namespace segments separated by colons:

```text
config:validate
migration:status
migration:run
installer:plan
module:list
runtime:doctor
```

Command names SHALL NOT be inferred from PHP class names.

## 5. Input boundary

The CLI SHALL parse process input into an immutable command input object.

The input model SHALL distinguish:

- command name;
- positional arguments;
- named options;
- boolean flags;
- repeated options;
- environment variables supplied by the process adapter;
- interaction capability;
- requested verbosity.

Command handlers SHALL NOT read `$_SERVER['argv']`, `STDIN` or environment variables directly.

The process adapter SHALL own translation from operating-system input into the CLI input model.

## 6. Output boundary

Commands SHALL write through an output contract rather than directly invoking `echo`, `printf` or terminal functions.

The output model SHALL distinguish:

- standard output;
- diagnostic output;
- warning output;
- error output;
- structured machine-readable records;
- verbosity levels.

Formatting SHALL be replaceable. Initial reference adapters MAY include plain-text and buffered output. ANSI decoration SHALL remain optional and capability-driven.

Sensitive values SHALL NOT be rendered by default. Commands that inspect configuration SHALL use the existing redaction and safe-summary policies.

## 7. Exit codes

The CLI SHALL return deterministic integer exit codes.

Initial governed categories SHALL include:

```text
0   success
1   command execution failure
2   invalid usage or input
3   command not found
4   validation failure
5   operation not authorized
6   requirements not satisfied
7   partial or compensated execution
8   internal CLI failure
```

Commands MAY return a more specific governed result, but process adapters SHALL map it to this stable category set.

Exceptions SHALL NOT be converted to success. Unknown exceptions SHALL be handled by the console kernel and mapped to an internal failure without exposing secrets.

## 8. Registry and resolution

Commands SHALL be registered explicitly in a deterministic registry.

The registry SHALL:

- reject duplicate canonical names;
- reject duplicate aliases;
- preserve deterministic listing order;
- resolve canonical names and aliases;
- expose immutable command metadata;
- avoid command instantiation through unrestricted filesystem scanning.

Service Providers MAY register commands through the CLI runtime composition boundary.

## 9. Console kernel

The console kernel SHALL coordinate exactly one invocation:

```text
parse input
    ↓
resolve command
    ↓
validate arguments and options
    ↓
execute handler
    ↓
render result
    ↓
return exit code
```

The kernel SHALL own:

- command-not-found behavior;
- usage validation failures;
- exception translation;
- execution timing;
- diagnostic context;
- final exit code.

The kernel SHALL NOT own installation, migration, module or persistence domain behavior.

## 10. Operational safety

Commands capable of modifying state SHALL declare themselves destructive or mutating.

Mutating commands SHALL support non-interactive execution and explicit authorization boundaries. A command SHALL NOT infer authorization merely because it is running in a terminal.

Where the underlying subsystem supports planning or dry-run, the CLI SHALL expose those modes rather than duplicating simulation logic.

Examples:

```text
installer:plan      → Installer planning contracts
installer:run       → authorized Installer execution
migration:plan      → Migration planning contracts
migration:run       → authorized Migration execution
```

The CLI SHALL preserve journaling, rollback, locking and transaction behavior from the underlying subsystems.

## 11. Initial command families

WP-221 SHALL establish command adapters for the following families when supported by stable existing contracts.

### 11.1 Runtime and diagnostics

```text
runtime:about
runtime:doctor
runtime:capabilities
```

### 11.2 Configuration

```text
config:validate
config:show
```

Configuration output SHALL be redacted and SHALL NOT expose secrets.

### 11.3 Migrations

```text
migration:status
migration:plan
migration:run
migration:rollback
```

Actual command availability SHALL reflect the migration runtime capabilities present in the application.

### 11.4 Installer

```text
installer:assess
installer:plan
installer:run
```

### 11.5 Modules and resources

```text
module:list
resource:inspect
```

Commands SHALL not claim unsupported enable, disable or publishing behavior until the corresponding subsystem contracts are stable.

## 12. Interaction policy

Interactive prompts SHALL be optional adapters, never mandatory command behavior.

Every production-relevant command SHALL support non-interactive operation with explicit options.

When interaction is unavailable:

- missing required input SHALL produce an invalid-usage result;
- confirmation SHALL not default to approval;
- destructive actions SHALL fail closed.

## 13. Observability

Each command execution SHOULD produce an operation context with:

- command name;
- invocation identifier;
- start and completion time;
- result category;
- correlation identifier when available.

Logging SHALL use existing structured logging contracts. Command arguments marked sensitive SHALL be redacted before observation.

The CLI SHALL not create a parallel logging subsystem.

## 14. Runtime integration

The CLI runtime SHALL be optional.

Application boot MAY register a CLI Service Provider and command registry, but SHALL NOT execute commands, read process input or write terminal output during boot.

Process execution SHALL be initiated explicitly by an entry point such as:

```text
bin/sif
bin/sif.bat
```

The existing `bin/sif-builder` remains a specialized governed engineering tool. WP-221 SHALL not silently repurpose or break it.

## 15. Compatibility and packaging

The first Developer CLI version SHALL target the PHP versions already governed by SIF and SHALL remain compatible with Windows PowerShell and Unix-like shells through thin platform entry points.

Command behavior SHALL be implemented in PHP. Shell and batch launchers SHALL contain no domain logic.

Public command names and exit-code categories SHALL be treated as compatibility-sensitive APIs.

## 16. Testing strategy

WP-221 SHALL include:

1. unit tests for immutable values and parser behavior;
2. registry and alias collision tests;
3. kernel execution and exit-code tests;
4. buffered I/O tests;
5. command adapter tests using subsystem doubles;
6. runtime integration tests proving boot has no command side effects;
7. entry-point smoke tests where environment support is available;
8. PHPStan level 8 validation;
9. SIF Builder validation and idempotence.

Tests SHALL NOT require an interactive terminal.

## 17. Delivery roadmap

WP-221 SHALL be delivered in eight increments:

### I1 — Architecture and command model

Defines boundaries, command identity, I/O, exit codes, safety and delivery sequence.

### I2 — Immutable CLI value model

Implements command names, argument and option definitions, invocation input, result and exit-code values.

### I3 — Registry, parser and help

Implements deterministic command registration, aliases, token parsing, usage validation, command listing and help rendering.

### I4 — Console kernel and reference I/O

Implements command execution orchestration, buffered/plain output, exception translation and timing.

### I5 — Runtime, configuration and diagnostic commands

Introduces safe informational and validation commands over Runtime, Environment and Configuration.

### I6 — Migration and Installer commands

Adds explicit plan, status, dry-run and authorized execution adapters over WP-216–WP-218.

### I7 — Module, resource, maintenance and extensibility commands

Adds supported operational adapters and command registration through Service Providers.

### I8 — Entry points, runtime integration and product completion

Adds `bin/sif`, Windows launcher, runtime composition, user documentation, compatibility review and product completion.

## 18. Acceptance criteria

WP-221 is complete when:

1. commands execute through an explicit console kernel;
2. command handlers are independent of global process state;
3. registry resolution and help are deterministic;
4. stable exit codes are tested;
5. mutating operations fail closed without authorization;
6. Installer and Migration logic remains delegated to their subsystems;
7. boot performs no command execution or terminal I/O;
8. Windows and Unix launchers remain thin;
9. PHPUnit and PHPStan pass;
10. governed documentation validates without diagnostics;
11. the CLI is suitable for non-interactive automation.
