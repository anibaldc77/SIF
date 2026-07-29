---
id: EG-302
title: Installation Execution, Journaling and Compensating Rollback
summary: Defines adapter-driven mutation execution, secret-safe append-only journals and reverse-order compensating rollback.
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
  - execution
  - rollback
depends_on:
  - EG-297
  - EG-301
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-302 — Installation Execution, Journaling and Compensating Rollback

## 1. Purpose

WP-216-I6 introduces controlled execution of an immutable `MutationPlan`. The core orchestrates handlers, validates their results, records a secret-safe journal and attempts compensating rollback after execution failure.

## 2. Mutation handlers

Execution SHALL occur through `MutationHandlerInterface` adapters. A handler SHALL:

- explicitly declare whether it supports a mutation descriptor;
- apply one supported mutation;
- compensate a previously applied mutation when rollback is supported;
- return immutable execution results containing fingerprints and diagnostic-safe scalar metadata only.

The core SHALL NOT contain direct filesystem, database, shell or network mutation logic.

Exactly one handler SHALL support each mutation at execution time. Missing or ambiguous handler selection SHALL fail before that mutation is applied.

## 3. Execution results

Every handler result SHALL identify the same mutation received by the handler and SHALL expose the expected status:

- `applied` after successful application;
- `compensated` after successful compensation.

Receipt values SHALL be represented only by lowercase SHA-256 fingerprints. Raw payloads, credentials, exception messages and secret values SHALL NOT be stored in execution results.

## 4. Journal

Execution SHALL produce an ordered immutable journal. Entries SHALL:

- use contiguous sequence numbers starting at one;
- preserve actual application and compensation order;
- contain immutable execution results;
- record failure type by class name without persisting exception messages.

The journal is an in-memory execution record. Durable journal persistence remains an adapter concern deferred beyond I6.

## 5. Failure and rollback

When application fails:

1. the failure SHALL be recorded;
2. previously applied mutations SHALL be visited in reverse application order;
3. supported compensation SHALL be attempted even if an earlier compensation fails;
4. unsupported rollback SHALL be recorded explicitly;
5. compensation failure SHALL be recorded without leaking the exception message.

Rollback is complete only when every previously applied mutation was compensated successfully. An empty applied set means rollback was not attempted.

## 6. Execution report

The final report SHALL contain:

- the immutable mutation-plan fingerprint;
- success or failure;
- failed mutation identifier when applicable;
- rollback attempted and completed flags;
- the ordered journal.

The report SHALL be safe for diagnostic serialization.

## 7. Safety boundaries

I6 SHALL NOT:

- resolve authorized roots by itself;
- provide a concrete filesystem or database handler;
- execute shell commands;
- persist journals;
- retry mutations automatically;
- resume interrupted executions;
- integrate application provisioning into Bootstrap.

## 8. Acceptance criteria

I6 is accepted when:

1. successful mutations execute in plan order;
2. missing and ambiguous handlers fail deterministically;
3. handler result identity and status are validated;
4. application failure initiates reverse-order compensation;
5. compensation continues after individual rollback failure;
6. unsupported rollback is represented explicitly;
7. journals exclude raw exception messages and payloads;
8. reports retain the original plan fingerprint;
9. PHPUnit and PHPStan pass.
