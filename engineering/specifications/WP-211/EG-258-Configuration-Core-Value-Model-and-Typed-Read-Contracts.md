---
id: EG-258
title: Configuration Core Value Model and Typed Read Contracts
summary: Defines immutable configuration keys, supported value types, explicit lookup results, exact typed reads, and an immutable compatibility-preserving repository for Configuration 2.0.
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
  - typed-reads
  - immutable-values
work_package: WP-211
depends_on:
  - EG-257
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-258 — Configuration Core Value Model and Typed Read Contracts

## Purpose

This specification defines the first production increment of Configuration 2.0.

It introduces an immutable key model, an explicit supported-value taxonomy, lookup results that distinguish missing keys from present `null` values, exact typed reads, and an immutable repository compatible with the existing WP-203 read contract.

## Compatibility boundary

The existing `ConfigurationInterface` remains valid and unchanged.

The new repository SHALL continue to provide:

- `has(string)`;
- `get(string, mixed)`;
- `require(string)`;
- `all()`;
- `isFrozen()`.

Dot notation SHALL remain supported.

No existing mutable configuration repository is replaced in this increment.

## Configuration keys

`ConfigurationKey` is an immutable value object.

Requirements:

- surrounding whitespace is normalized;
- empty keys are rejected;
- empty dot-notation segments are rejected;
- normalized value and ordered segments are exposed;
- equality is value based.

## Supported values

The core value taxonomy contains:

- `null`;
- boolean;
- integer;
- float;
- string;
- arrays recursively containing supported values.

Objects, resources, and callables are unsupported.

No implicit scalar conversion occurs.

## Lookup results

`ConfigurationLookupResult` explicitly represents:

- found value;
- missing key.

A found `null` value SHALL remain distinguishable from a missing key.

The result exposes:

- key;
- found/missing state;
- guarded value access;
- fallback access;
- required access using the existing not-found exception.

## Typed read contract

`TypedConfigurationInterface` extends the existing read contract with:

- `lookup`;
- `string`;
- `integer`;
- `float`;
- `boolean`;
- `array`;
- `nullableString`.

Typed reads are exact.

Examples:

- string `"1"` is not an integer;
- integer `1` is not a float;
- string `"true"` is not a boolean.

A mismatch raises `ConfigurationTypeMismatchException`.

## Immutable repository

`ImmutableConfigurationRepository`:

- validates all values at construction;
- copies the root array by value under PHP array semantics;
- is permanently frozen;
- supports dot notation;
- preserves present `null` values;
- implements both legacy and typed reads;
- performs no I/O;
- has no mutable global state.

## Exclusions

This increment does not implement:

- source providers;
- layered composition;
- precedence metadata;
- provenance;
- schemas;
- secret classification;
- redaction;
- snapshots or fingerprints;
- caching;
- Runtime replacement;
- Container registration.

## Acceptance criteria

- existing configuration tests remain green;
- key normalization is deterministic;
- unsupported nested values fail;
- missing and present-null are distinguishable;
- typed reads never coerce values;
- immutable repository satisfies `ConfigurationInterface`;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
