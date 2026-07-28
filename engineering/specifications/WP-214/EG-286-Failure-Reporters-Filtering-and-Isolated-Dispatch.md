---
id: EG-286
title: Failure Reporters, Filtering and Isolated Dispatch
summary: Defines provider-neutral failure reporters, deterministic filtering and routing, isolated reporter failures and a terminal emergency boundary.
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
  - reporting
  - filtering
  - routing
  - failure-isolation
  - foundation
work_package: WP-214
depends_on:
  - EG-281
  - EG-282
  - EG-283
  - EG-284
  - EG-285
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-286 — Failure Reporters, Filtering and Isolated Dispatch

## Purpose

Define provider-neutral reporting contracts and deterministic isolated dispatch for immutable failure envelopes and recovery decisions.

## Requirements

1. A reporter receives the original `FailureEnvelope` and `RecoveryDecision`.
2. Routes are named, ordered, unique, filtered and immutable.
3. Filters may select by minimum severity, category, or composed AND semantics.
4. Every matching route is evaluated in declaration order.
5. A reporter failure is captured without replacing the original application failure.
6. Later routes continue after an ordinary reporter failure.
7. A reduced emergency reporter receives the reporter name, envelope, decision and original reporter failure.
8. Emergency reporter failures are swallowed at a terminal boundary and SHALL NOT recurse through ordinary reporting.
9. Dispatch returns an immutable result describing reported, filtered and failed routes.
10. No reporter performs recovery, retry scheduling, sleeping, classification or envelope construction.

## Components

- `FailureReporterInterface`
- `FailureReportFilterInterface`
- `EmergencyFailureReporterInterface`
- `FailureReportRoute`
- `FailureReporterDispatcher`
- `FailureReportingResult`
- `FailureReporterFailure`
- standard filters
- in-memory and null emergency reference implementations

## Safety boundary

The dispatcher catches only failures from ordinary reporters. Filter failures represent invalid composition or implementation defects and are not hidden. Emergency reporting is terminal: any failure raised there is absorbed and never reintroduced into the same reporting pipeline.

## Deferred scope

- logging reporter adapter;
- audit reporter adapter;
- immutable `ErrorHandlingPlan`;
- end-to-end orchestration;
- Runtime service-provider integration.
