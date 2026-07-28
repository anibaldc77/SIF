---
id: EG-261
title: Configuration Schema Validation and Normalization
summary: Defines explicit schema contracts, structured validation results, and side-effect-free normalization for Configuration 2.0.
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
  - schema
  - validation
  - normalization
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
  - EG-259
  - EG-260
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-261 — Configuration Schema Validation and Normalization

## Purpose

This specification defines an explicit and source-neutral schema layer for Configuration 2.0. Schema validation SHALL operate on the immutable typed repository produced by composition and SHALL return a separate immutable repository containing normalized values.

## Schema contracts

A configuration schema SHALL expose an ordered list of rules. Each rule SHALL define:

- one normalized configuration key;
- one expected `ConfigurationValueType`;
- whether the key is required;
- whether an explicit `null` value is accepted;
- an optional normalizer contract.

Duplicate rules for the same key SHALL be rejected explicitly.

## Validation behavior

Validation SHALL be deterministic and SHALL preserve rule order in the resulting issue list.

A missing required key SHALL produce `CFG_SCHEMA_REQUIRED_KEY_MISSING`.

A present value whose runtime type differs from the expected type SHALL produce `CFG_SCHEMA_TYPE_MISMATCH`.

Validation SHALL NOT coerce strings into numbers or booleans. Strict source parsing remains the responsibility of source adapters.

Optional missing keys SHALL be accepted. Explicit `null` SHALL be accepted only when the rule is nullable.

## Normalization

A normalizer SHALL implement `ConfigurationNormalizerInterface` and SHALL transform only the value associated with its rule.

Normalization SHALL occur before type verification. The original repository SHALL remain unchanged. The validation result SHALL expose a newly constructed immutable repository containing normalized values and all unrelated configuration values.

Built-in normalizers introduced by this increment are:

- `TrimStringNormalizer`;
- `LowercaseStringNormalizer`.

These normalizers SHALL leave non-string values unchanged so that normal type validation can report mismatches.

## Validation result

`ConfigurationValidationResult` SHALL expose:

- whether validation succeeded;
- the normalized immutable repository;
- an ordered list of `ConfigurationValidationIssue` instances.

A validation issue SHALL contain only a stable code, a safe message, and the affected configuration key. It SHALL NOT contain the rejected value.

## Compatibility

This increment SHALL NOT modify existing repositories, source contracts, precedence, provenance, diagnostics, loaders, Runtime integration, or Container integration.

## Exclusions

This increment does not implement:

- arbitrary callback constraints;
- cross-key validation;
- default insertion;
- enumerated or range constraints;
- secret classification;
- Runtime boot failure integration;
- Container parameter binding;
- schema discovery or caching.

## Acceptance criteria

- schemas expose deterministic ordered rules;
- duplicate keys are rejected;
- required missing keys produce structured issues;
- strict type mismatches produce structured issues without coercion;
- optional and nullable rules behave explicitly;
- normalization produces a new immutable repository;
- source repositories remain unchanged;
- validation issues contain no configuration values;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
