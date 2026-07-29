---
id: EG-300
title: Installation Step Registry and Dependency Planning
summary: Defines explicit installation step contributions, duplicate detection, dependency validation, cycle rejection and deterministic topological ordering.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-216
tags:
  - foundation
  - installer
  - steps
  - dependency-planning
depends_on:
  - EG-297
  - EG-298
  - EG-299
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-300 — Installation Step Registry and Dependency Planning

## 1. Purpose

WP-216-I4 introduces explicit installation-step contributions and deterministic dependency planning. It does not describe concrete mutations or execute steps.

## 2. Step contract

A step declares a stable identifier, bounded description, integer priority, dependencies, mutation classification, idempotency and rollback policy. The contract contains metadata only in this increment.

## 3. Registration

Registration is explicit. Duplicate step identifiers and duplicate dependency declarations fail with typed exceptions. Self-dependencies are invalid.

## 4. Dependency semantics

Required missing dependencies fail compilation. Optional missing dependencies are ignored. When an optional dependency is registered, it participates in ordering exactly like a required dependency.

## 5. Deterministic ordering

The planner performs a stable topological ordering. Dependencies always precede dependents. Among simultaneously ready steps, lower integer priority runs first and registration order breaks ties.

## 6. Cycle handling

Cycles fail plan compilation. Diagnostics list remaining step identifiers in locale-independent lexical order.

## 7. Compiled plan

`InstallationStepPlan` is immutable, preserves unique ordered steps and exposes a secret-free metadata summary.

## 8. Safety and scope

Planning performs no filesystem, configuration, database, network or runtime mutation. Executable behavior is deferred to I6.

## 9. Acceptance criteria

I4 is accepted when duplicates, missing required dependencies, self-dependencies and cycles fail deterministically; optional dependencies behave explicitly; topological ordering is stable; focused tests and PHPStan succeed.
