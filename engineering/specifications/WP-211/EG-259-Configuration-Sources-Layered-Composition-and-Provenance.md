---
id: EG-259
title: Configuration Sources, Layered Composition, and Provenance
summary: Defines source-neutral configuration inputs, deterministic precedence ordering, compatibility merge semantics, and leaf-level provenance for Configuration 2.0.
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
  - composition
  - provenance
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-259 — Configuration Sources, Layered Composition, and Provenance

## Purpose

This specification defines the second production increment of Configuration 2.0.

It introduces source-neutral configuration contracts, an in-memory array source, deterministic layered composition, compatibility-preserving merge semantics, and safe leaf-level provenance.

## Source contract

A configuration source SHALL expose:

- a non-empty identifier;
- a non-empty type;
- an integer precedence;
- a load operation returning a validated source result.

A source SHALL produce values without mutating a repository.

The source result SHALL contain only values accepted by the Configuration 2.0 value model.

## Initial source implementation

`ArrayConfigurationSource` is the reference source for:

- application defaults;
- tests;
- programmatic overrides;
- adapters that already hold normalized values.

It performs no I/O and has no Runtime dependency.

## Ordering

Composition SHALL order sources from lower to higher precedence.

When two sources have equal precedence, stable registration order SHALL determine the winner. A source registered later at the same precedence SHALL override an earlier source.

Ordering SHALL be independent of the order in which a source completes loading.

## Merge policy

The default merge policy SHALL remain compatible with WP-203:

- associative maps merge recursively;
- lists replace complete earlier lists;
- scalar values replace earlier values;
- scalar/map conflicts are resolved by the later source;
- an empty incoming array replaces the earlier value.

The existing `ConfigurationMerger` SHALL remain the default strategy in this increment.

## Composed result

Composition SHALL return a `ComposedConfiguration` containing:

- an `ImmutableConfigurationRepository`;
- provenance indexed by normalized dot-notation key.

The composed repository SHALL remain permanently frozen and SHALL implement the typed and legacy read contracts.

## Provenance

`ConfigurationProvenance` SHALL expose safe metadata only:

- normalized key;
- source identifier;
- source type;
- precedence;
- original registration order;
- whether the selected value overrode an earlier leaf value.

Provenance SHALL NOT contain configuration values.

In this increment, provenance is recorded for terminal values, lists, and empty arrays. Intermediate associative-map nodes do not receive independent provenance entries.

## Failure behavior

Empty source identifiers and source types SHALL fail explicitly.

Unsupported values SHALL fail during source-result validation before a composed repository is produced.

Partial composed results SHALL NOT be returned after a failure.

## Exclusions

This increment does not implement:

- PHP or JSON file source adapters;
- environment or command-line sources;
- diagnostics collections;
- schema validation;
- secret classification or redaction;
- snapshots or fingerprints;
- source caching;
- Runtime replacement;
- Container registration.

## Acceptance criteria

- source contracts remain independent of Runtime and Application;
- source values are validated before composition;
- precedence ordering is deterministic;
- equal precedence preserves registration order;
- WP-203 merge behavior remains unchanged;
- final leaf provenance identifies the selected source;
- provenance never stores values;
- the output repository is immutable;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
