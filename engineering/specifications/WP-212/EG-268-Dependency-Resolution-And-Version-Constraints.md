---
id: EG-268
title: Module Dependency Resolution and Version Constraints
summary: Defines deterministic version constraints, dependency graph analysis, conflict detection, and cycle rejection for Module Registry 2.0.
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
  - dependencies
  - semver
  - graph
  - resolution
depends_on:
  - EG-265
  - EG-266
  - EG-267
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-268 — Module Dependency Resolution and Version Constraints

## 1. Purpose

WP-212-I4 introduces deterministic version constraint evaluation and dependency graph analysis for registered module descriptors.

The increment SHALL validate required dependencies, compatible optional dependencies, declared conflicts, deterministic ordering, and cycles before any module contribution is applied.

## 2. Scope

The increment includes:

- immutable version constraints;
- deterministic matching against `ModuleVersion`;
- a dependency resolver contract;
- required dependency validation;
- optional dependency ordering when present and compatible;
- conflict validation;
- stable topological ordering;
- cycle detection;
- immutable graph analysis output;
- typed failures and tests.

The increment excludes enablement policies, disabled-module reasoning, final activation plans, capability resolution, contribution execution, Runtime integration, fingerprints, and caching.

## 3. Constraint grammar

The initial grammar SHALL support:

- wildcard `*`;
- exact semantic versions;
- comparison operators `=`, `>`, `>=`, `<`, and `<=`;
- comma-separated conjunctive ranges;
- caret-compatible ranges;
- tilde-compatible ranges;
- major and major-minor wildcards using `*`, `x`, or `X`.

Constraints SHALL contain no whitespace and SHALL be validated before matching.

Constraint evaluation SHALL use `ModuleVersion::compareTo()` and SHALL not depend on Composer or another third-party runtime parser.

## 4. Dependency validation

A required dependency SHALL:

1. exist in the registry;
2. satisfy its declared version constraint;
3. contribute an ordering edge from dependency to dependent.

A missing required dependency SHALL fail analysis with a typed exception.

An incompatible required dependency SHALL fail analysis with a typed exception that identifies module IDs, expected constraint, and actual version without exposing arbitrary objects.

## 5. Optional dependencies

An optional dependency SHALL contribute an ordering edge only when:

- the target module is registered; and
- the registered version satisfies the declared constraint.

A missing or incompatible optional dependency SHALL not fail graph analysis in I4.

## 6. Conflicts

A declared conflict SHALL fail graph analysis when the target module is registered and its version matches the conflict constraint.

Conflict evaluation SHALL occur before successful analysis is returned and before any module contribution is applied.

## 7. Deterministic topological order

The graph SHALL orient dependency edges from dependency to dependent.

The resolver SHALL use stable registration order for simultaneously available nodes. Canonical module ID SHALL be the final tie-breaker.

Equivalent registry input SHALL produce equivalent order and dependency-edge output.

## 8. Cycle detection

If topological ordering cannot consume every registered module, analysis SHALL fail with `ModuleDependencyCycleException`.

The reported participant list SHALL be deterministic and SHALL contain safe canonical module identifiers only.

I4 does not require extraction of one minimal cycle path; it requires deterministic rejection of the cyclic remainder.

## 9. Analysis result

`DependencyGraphAnalysis` SHALL be immutable and SHALL expose:

- ordered module descriptors;
- normalized dependency IDs by module.

It SHALL NOT be treated as the final `ResolvedModulePlan`, which belongs to WP-212-I5.

## 10. Safety and compatibility

The resolver SHALL NOT:

- instantiate module services;
- execute providers;
- mutate the registry;
- read configuration or secrets;
- scan the filesystem;
- access the network;
- modify Runtime state.

Existing module descriptors and registry contracts remain compatible.

## 11. Acceptance criteria

WP-212-I4 is accepted when:

1. supported constraints match deterministically;
2. required dependencies are validated and ordered;
3. missing and incompatible required dependencies fail with typed exceptions;
4. compatible optional dependencies influence order;
5. missing or incompatible optional dependencies do not fail;
6. matching conflicts fail;
7. independent modules preserve registration order;
8. cycles fail deterministically;
9. PHPStan level 8 reports no errors;
10. the complete repository quality gate passes.
