---
id: EG-287
title: Immutable ErrorHandlingPlan and Orchestration
summary: Defines provider-neutral orchestration for classification, envelope creation, recovery decisions, and isolated reporting.
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
  - orchestration
  - recovery
work_package: WP-214
depends_on:
  - EG-286
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-287 — Immutable ErrorHandlingPlan and Orchestration

## Purpose

This increment composes the previously independent error-handling capabilities without coupling the subsystem to a framework runtime, logger, container, or provider.

## Components

- `ErrorHandlingPlan` is an immutable dependency plan.
- `ErrorHandlerInterface` defines the provider-neutral entry point.
- `ErrorHandler` performs deterministic stage composition.
- `ErrorHandlingResult` preserves all products of the operation.

## Orchestration order

1. Classify the original `Throwable`.
2. Create an immutable `FailureEnvelope`.
3. Decide recovery for the supplied attempt.
4. Dispatch the envelope and decision to reporting routes.
5. Return one immutable result.

The same classification feeds envelope creation and recovery. The same envelope and decision are supplied to reporting.

## Boundary

The orchestrator does not sleep, retry, rethrow, abort, log directly, mutate runtime state, resolve dependencies, or use globals. Recovery execution remains the responsibility of the caller.

## Invariants

- Attempts are one-based.
- The original throwable identity is preserved.
- Reporting failures remain isolated by the dispatcher.
- Results expose classification, envelope, recovery decision, and reporting result.
- Summary projection is structured and provider neutral.
