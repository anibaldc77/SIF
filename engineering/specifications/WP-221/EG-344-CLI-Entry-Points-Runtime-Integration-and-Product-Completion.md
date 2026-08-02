---
id: EG-344
title: CLI Entry Points Runtime Integration and Product Completion
summary: Defines the developer CLI runtime composition, application integration, launchers and final product boundaries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-221
tags:
  - foundation
  - cli
  - runtime
  - entry-point
depends_on:
  - EG-337
  - EG-338
  - EG-339
  - EG-340
  - EG-341
  - EG-342
  - EG-343
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-344 — CLI Entry Points, Runtime Integration and Product Completion

## 1. Purpose

I8 completes the Developer CLI by introducing an explicitly composed `CliRuntime`, optional Application and Bootstrap integration, and thin Unix and Windows launchers.

## 2. Runtime composition

`CliRuntime` SHALL own the console kernel, command registry and help catalog. The default factory SHALL register only safe Runtime inspection commands. Additional commands SHALL be contributed explicitly.

## 3. Application integration

Applications MAY expose a CLI runtime through dedicated aware and mutable contracts. The service provider SHALL publish `cli` and `cli.developer` capabilities without executing commands or reading process globals.

## 4. Entry points

`bin/sif` and `bin/sif.bat` SHALL remain thin launchers. A project MAY provide `bootstrap/cli.php` returning a configured `CliRuntime`. In its absence, the launcher SHALL use a minimal safe runtime.

## 5. Safety

Bootstrapping SHALL NOT execute mutations, migrations, Installer operations or maintenance actions. Mutating commands SHALL remain unavailable unless explicitly contributed with their required authorizers and services.

## 6. Completion

WP-221 is complete when value objects, parsing, registry, help, console kernel, I/O adapters, operational commands, extension points, runtime integration and launchers pass the full quality gate.
