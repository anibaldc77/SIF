---
id: EG-021
title: Application and Core Commands
summary: This increment implements command dispatch and the informational commands that do not require Builder Engine composition. It keeps the CLI independent from terminal globals and from the concrete Engine dependency graph.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - application
  - core
  - commands
work_package: WP-104
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-021 — Application and Core Commands

- **Work Package:** WP-104
- **Increment:** 3 of 6
- **Status:** Ready for integration
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** EG-018, EG-019, EG-020
- **Target:** SIF Builder v2.0.0-alpha1

## 1. Purpose

This increment implements command dispatch and the informational commands that do not require Builder Engine composition. It keeps the CLI independent from terminal globals and from the concrete Engine dependency graph.

## 2. Scope

The increment introduces:

- `CliApplicationInterface` and `CliApplication`;
- `VersionProviderInterface` and `StaticVersionProvider`;
- `ComponentCatalogInterface` and `StaticComponentCatalog`;
- `HelpCommand`;
- `VersionCommand`;
- `ListCommand`;
- application and command tests.

The `build` and `validate` commands are deferred to Increment 4.

## 3. Application dispatch

`CliApplication` receives an `ArgumentParserInterface` and `CommandRegistry`. On every run it:

1. freezes the command registry;
2. parses immutable raw input;
3. looks up the exact command;
4. executes the command once;
5. returns its `CommandResult`.

Expected parse failures and unknown commands map to `INVALID_USAGE`. Unexpected exceptions map to `INTERNAL_ERROR` without exposing exception messages or stack traces.

The application does not write streams and does not terminate the process.

## 4. Version source

Version output is provided through `VersionProviderInterface`. The version string must not be duplicated across commands or executable scripts.

`StaticVersionProvider` validates both the application name and release string and is suitable for the initial composition root and tests.

## 5. Component catalog

`ListCommand` depends on `ComponentCatalogInterface`, not directly on Engine registries. The default Engine composition introduced in Increment 4 may adapt the real analyzer, generator, and reporter registrations to this contract.

Identifiers are ordered and normalized. Duplicate identifiers are rejected.

## 6. Core commands

### 6.1 Help

```text
sif-builder help
sif-builder help <command>
```

Global help lists commands in registry insertion order. Command-specific help displays the registered name and description. Unknown command targets map to `INVALID_USAGE`.

### 6.2 Version

```text
sif-builder version
```

The command renders `<application-name> <version>` followed by a newline. Additional input is rejected.

### 6.3 List

```text
sif-builder list
```

The command renders analyzers, generators, and reporters in deterministic catalog order. Empty categories are rendered explicitly as `(none)`. Additional input is rejected.

## 7. Deferred work

This increment excludes:

- Builder request mapping;
- Engine factory and composition root;
- `build` and `validate` commands;
- reporter selection and exit mapping;
- stdout/stderr adapters;
- executable scripts;
- global `--help` or `--version` aliases without a command name.

## 8. Public API

The following are public:

- `CliApplicationInterface`;
- `VersionProviderInterface`;
- `ComponentCatalogInterface`;
- the command names `help`, `version`, and `list`;
- their deterministic success payload structure;
- safe mapping of unexpected failures to `INTERNAL_ERROR`.

Changes require compatibility review.

## 9. Acceptance criteria

The increment is accepted when:

1. registered commands are dispatched exactly once;
2. the registry is frozen before command execution;
3. parse failures and unknown commands map to `INVALID_USAGE`;
4. unexpected exceptions map to safe `INTERNAL_ERROR` results;
5. help output preserves registry insertion order;
6. version output is supplied by an injected provider;
7. list output preserves catalog ordering;
8. all commands reject unsupported input;
9. tests pass under PHP 8.2 and PHPUnit 10;
10. PHPStan level 8 reports no errors.
