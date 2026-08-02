---
id: EG-341
title: Runtime Configuration and Diagnostic Commands
summary: Defines read-only runtime inspection, capability reporting, configuration validation and diagnostic commands for the SIF Developer CLI.
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
  - runtime
  - configuration
  - diagnostics
depends_on:
  - EG-337
  - EG-338
  - EG-339
  - EG-340
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-341 — Runtime, Configuration and Diagnostic Commands

## 1. Purpose

I5 introduces the first operational commands of the SIF Developer CLI. The commands are inspection and validation adapters over existing runtime and configuration contracts. They do not own application boot, configuration loading or mutation logic.

## 2. Runtime commands

`runtime:about` SHALL expose only safe runtime state, boot stage, failure presence and capability count. `runtime:capabilities` SHALL return a sorted, duplicate-free list supplied by the composed application boundary.

`runtime:doctor` SHALL execute deterministic non-mutating checks and SHALL produce a structured diagnostic report. The alias `runtime:diagnose` SHALL resolve to the same command.

## 3. Configuration validation

`config:validate` SHALL reuse `ConfigurationSchemaValidator`, `TypedConfigurationInterface` and `ConfigurationSchemaInterface`. Validation issues SHALL preserve governed codes, keys and messages. Invalid configuration SHALL return exit code 4.

## 4. Security and side effects

Commands SHALL NOT expose configuration values, environment secrets, DSNs or driver details. I5 SHALL NOT freeze configuration, mutate repositories, boot the runtime, execute SQL or perform installation.

## 5. Result semantics

Successful inspection returns exit code 0. Runtime or configuration validation failures return exit code 4. Results SHALL remain structured so text and JSON renderers can represent them without inspecting command internals.

## 6. Scope boundary

Migration and Installer commands remain reserved for I6. Module, resource and maintenance commands remain reserved for I7.
