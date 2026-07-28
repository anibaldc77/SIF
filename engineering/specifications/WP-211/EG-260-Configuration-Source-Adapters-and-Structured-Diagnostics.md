---
id: EG-260
title: Configuration Source Adapters and Structured Diagnostics
summary: Defines file and explicitly mapped environment configuration sources together with safe, composable diagnostics for Configuration 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
tags:
  - foundation
  - configuration
  - sources
  - environment
  - diagnostics
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
  - EG-259
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-260 — Configuration Source Adapters and Structured Diagnostics

## Purpose

This specification defines file and environment adapters for the source-neutral model introduced by EG-259. It also defines structured diagnostics that can be aggregated without embedding configuration values.

## File source

`FileConfigurationSource` SHALL adapt the existing `ConfigurationFileLoader` rather than duplicate PHP or JSON parsing.

A required file SHALL preserve existing explicit source failures. An optional missing file SHALL contribute an empty value set and a warning diagnostic.

The adapter SHALL expose stable source identity, type, and precedence.

## Environment source

`EnvironmentConfigurationSource` SHALL read only variables declared through `EnvironmentVariableDefinition` instances. It SHALL NOT import the complete process environment or infer configuration keys from arbitrary variable names.

A definition SHALL include:

- environment variable name;
- normalized configuration key;
- expected value type;
- required flag;
- optional default value.

String, integer, float, and boolean parsing SHALL be strict. Unsupported or invalid conversions SHALL fail explicitly. Required missing variables SHALL fail explicitly. Optional missing variables MAY contribute a default and SHALL emit a warning diagnostic.

Environment variable values SHALL never be included in diagnostics.

## Structured diagnostics

A `ConfigurationDiagnostic` SHALL contain:

- non-empty stable code;
- severity;
- safe human-readable message;
- source identifier;
- scalar, non-secret context metadata.

Diagnostic severity SHALL be one of `info`, `warning`, or `error`.

`ConfigurationSourceResult` SHALL expose zero or more diagnostics. `ConfigurationComposer` SHALL aggregate source diagnostics in deterministic source-processing order. `ComposedConfiguration` SHALL expose the aggregated list without changing repository or provenance semantics.

## Compatibility

The extension of `ConfigurationSourceResult` and `ComposedConfiguration` SHALL use optional constructor arguments so existing callers remain source compatible.

The existing loaders, repository contracts, merge policy, precedence ordering, and provenance model SHALL remain unchanged.

## Security constraints

Diagnostics SHALL NOT contain configuration values, environment values, parsed secrets, or full environment snapshots.

File paths and environment variable names MAY be included as operational metadata. Secret classification and redaction policy remain reserved for a later increment.

## Exclusions

This increment does not implement:

- command-line sources;
- directory discovery;
- `.env` parsing;
- schema validation;
- secret classification;
- snapshots or fingerprints;
- source caching;
- Runtime or Container integration.

## Acceptance criteria

- PHP and JSON files are loaded through existing loaders;
- required source failures remain explicit;
- optional missing files produce warnings and no values;
- environment imports are allow-listed by explicit definitions;
- typed environment parsing is strict;
- required missing environment variables fail explicitly;
- diagnostics contain no configuration values;
- composition aggregates diagnostics deterministically;
- existing WP-203 and WP-211 behavior remains compatible;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
