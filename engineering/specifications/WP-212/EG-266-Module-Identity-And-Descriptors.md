---
id: EG-266
title: Module Identity, Version and Descriptor Model
summary: Defines the immutable value model and foundational contracts for Module Registry 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-212
tags:
  - foundation
  - modules
  - identity
  - descriptors
depends_on:
  - EG-265
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-266 — Module Identity, Version and Descriptor Model

## 1. Purpose

WP-212-I2 establishes the immutable declarative model required by Module Registry 2.0. It introduces no registry implementation, graph resolution, lifecycle execution, automatic discovery, or Runtime mutation.

## 2. Deliverables

The increment SHALL provide:

- canonical `ModuleId`;
- semantic-version-compatible `ModuleVersion`;
- required and optional `ModuleDependency` declarations;
- `ModuleConflict` declarations;
- immutable `ModuleDescriptor`;
- descriptor-provider and module contracts;
- read-only and mutable registry boundary contracts;
- typed exceptions and unit tests.

## 3. Identity rules

`ModuleId` SHALL be non-empty, case-sensitive, portable, whitespace-free, and independent of paths, namespaces, package names, or display labels.

The accepted character set SHALL be ASCII letters, decimal digits, period, underscore, and hyphen. The first character SHALL be alphanumeric.

## 4. Version rules

`ModuleVersion` SHALL accept normalized semantic versions composed of major, minor, and patch components, with optional pre-release and build metadata.

Comparison SHALL follow semantic-version precedence. Build metadata SHALL NOT alter precedence. Invalid leading zeroes and ambiguous abbreviated versions SHALL fail during construction.

## 5. Relationship declarations

Dependencies and conflicts SHALL identify another module and carry an explicit version-constraint token. Constraint interpretation is deferred to the resolver increment.

A descriptor SHALL reject:

- self-dependencies and self-conflicts;
- duplicate dependency or conflict targets;
- a target declared simultaneously as dependency and conflict.

## 6. Descriptor safety

`ModuleDescriptor` SHALL remain declarative and immutable. Its construction SHALL perform validation only and SHALL NOT access Runtime, Container, Configuration, network, filesystem discovery, or module boot logic.

Diagnostic metadata SHALL be restricted to scalar or null values.

## 7. Contracts

The increment defines contracts for:

- descriptor providers;
- concrete modules;
- read-only registry inspection;
- controlled mutable registration.

These contracts establish boundaries only. Their concrete registry behavior is deferred.

## 8. Compatibility

I2 adds isolated PSR-4 classes under `Sif\Foundation\Modules` and changes no existing public constructor or Runtime behavior.

## 9. Acceptance criteria

The increment is acceptable when:

- all value objects reject invalid state;
- semantic-version precedence is deterministic;
- descriptors expose immutable metadata;
- contradictory relationships fail with typed exceptions;
- PHPUnit passes in the target environment;
- PHPStan level 8 reports no errors;
- Builder validation reports no diagnostics.
