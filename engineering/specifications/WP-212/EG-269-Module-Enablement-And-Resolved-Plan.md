---
id: EG-269
title: Module Enablement Policy and Resolved Module Plan
summary: Defines explicit module enablement decisions, safe disablement reasons, successful registry freeze, and immutable resolved module plans.
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
  - enablement
  - planning
  - resolution
depends_on:
  - EG-265
  - EG-266
  - EG-267
  - EG-268
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-269 — Module Enablement Policy and Resolved Module Plan

## 1. Purpose

WP-212-I5 introduces deterministic enablement evaluation and publication of an immutable resolved module plan before any module contribution is applied.

## 2. Scope

The increment includes explicit enablement decisions, safe disablement reason codes, policy evaluation in registration order, validation of disabled required dependencies, immutable enabled and disabled module views, dependency edges, reverse shutdown order, and registry freeze after successful resolution.

The increment excludes capability resolution, configuration and container contributions, Service Provider execution, Runtime integration, diagnostics aggregation, canonical fingerprints, and caching.

## 3. Enablement decisions

A decision SHALL be either enabled without a reason or disabled with a non-empty safe reason code.

Reason codes SHALL be diagnostic identifiers and SHALL NOT contain secrets, configuration values, paths, arbitrary object dumps, or deployment-sensitive content.

## 4. Explicit policy

The initial policy SHALL support decisions keyed by canonical `ModuleId` and an optional deterministic default decision.

When no explicit or default decision exists, a registered module SHALL be disabled using `MODULE_NOT_EXPLICITLY_ENABLED`.

Presence in the registry SHALL NOT implicitly enable a module.

## 5. Resolution semantics

Policy evaluation SHALL occur before dependency graph analysis.

Only enabled modules SHALL participate in dependency ordering, conflict evaluation, and plan activation order.

A disabled optional dependency SHALL be ignored.

An enabled module requiring a registered but disabled module SHALL fail with a typed exception before plan publication.

Failures SHALL leave the source registry mutable.

## 6. Resolved plan

A successful resolution SHALL produce an immutable `ResolvedModulePlan` exposing:

- enabled descriptors in deterministic dependency order;
- disabled descriptors in stable registration order;
- safe disablement reason codes;
- dependency edges for enabled modules;
- reverse shutdown order.

The plan SHALL NOT expose service instances, closures, secrets, configuration values, or mutable registry state.

## 7. Registry lifecycle

The source registry SHALL freeze only after dependency analysis succeeds and immediately before the plan is returned.

Successful resolution SHALL therefore prevent subsequent module registration. Rejected resolution SHALL NOT freeze the registry.

## 8. Acceptance criteria

WP-212-I5 is complete when explicit and default policy behavior, safe reason invariants, dependency ordering, disabled required and optional dependency semantics, reverse shutdown order, successful freeze, failure non-freeze, PHPStan level 8, and repository governance validation pass.
