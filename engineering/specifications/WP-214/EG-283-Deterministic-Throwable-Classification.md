---
id: EG-283
title: Deterministic Throwable Classification
summary: Defines ordered provider-neutral throwable classification with explicit fallback semantics.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-28
updated: 2026-07-28
tags:
  - error-handling
  - classification
  - throwable
  - foundation
work_package: WP-214
depends_on:
  - EG-281
  - EG-282
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-283 — Deterministic Throwable Classification

## Status

Accepted for WP-214-I3.

## Objective

Classify a `Throwable` through an explicit, ordered and provider-neutral rule set. Classification assigns category, severity and disposition, but never chooses a recovery action.

## Invariants

1. Rules are evaluated in declaration order.
2. The first matching rule wins.
3. Rule names are unique portable identifiers.
4. A classifier always returns a result through an explicit fallback.
5. Classification never mutates or replaces the original `Throwable`.
6. Rules do not perform logging, auditing, I/O, sleeping or retry execution.
7. Unknown failures default to category `unknown`, severity `error` and disposition `unknown`.

## Contracts

- `ThrowableClassificationRuleInterface`
- `ThrowableClassifierInterface`

## Reference rule

`InstanceOfThrowableClassificationRule` performs inheritance-aware matching with `instanceof`. More specialized rules must be declared before broader parent-type rules.

## Result

`ThrowableClassification` is immutable and exposes a canonical summary containing category, severity, disposition, matched rule and fallback status.

## Deferred

- recovery decisions;
- retry guidance;
- envelope factory composition;
- metadata normalization and redaction;
- reporters and runtime integration.
