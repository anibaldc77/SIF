---
id: EG-284
title: Recovery Decisions, Ordered Policies and Retry Guidance
summary: Defines immutable recovery decisions, ordered policies, attempt limits and deterministic retry-delay guidance.
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
  - recovery
  - retry
  - policy
  - foundation
work_package: WP-214
depends_on:
  - EG-281
  - EG-282
  - EG-283
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-284 — Recovery Decisions, Ordered Policies and Retry Guidance

## Status
Implemented by WP-214-I4.

## Objective
Define provider-neutral and deterministic recovery decisions without executing retries, sleeping, scheduling or controlling processes.

## Model
A `RecoveryDecision` contains an action, the policy that produced it, optional retry guidance and a fallback marker. Supported actions are `continue`, `degrade`, `retry`, `abort` and `rethrow`.

`RetryGuidance` is descriptive only. It records the current one-based attempt, maximum attempts and deterministic delay in milliseconds. The caller owns waiting, scheduling and invoking the operation again.

## Policies
`OrderedRecoveryDecider` evaluates policies in declaration order and returns the first decision. Duplicate names are rejected. If no policy applies, an explicit fallback decision is returned.

`TransientRetryRecoveryPolicy` applies only to transient classifications. It returns retry guidance while the attempt is below the limit and a configured terminal action when the limit is reached.

`DispositionRecoveryPolicy` maps one disposition to a non-retry action.

## Delay strategies
Fixed and bounded exponential delay strategies are deterministic and contain no randomness or side effects.

## Boundaries
This increment does not sleep, retry, enqueue jobs, implement circuit breakers, report failures, or integrate with Runtime lifecycle.
