---
id: WP-205-I2A-REVIEW
title: WP-205-I2A Runtime Event Observation Architecture Review
summary: Reviews the corrective non-invasive architecture proposed after rejecting embedded dispatcher integration in the runtime core.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-24
updated: 2026-07-24
work_package: WP-205
tags:
  - runtime
  - events
  - architecture
  - compatibility
  - review
depends_on:
  - EG-214-A1
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-205-I2A — Architecture Review

## 1. Review context

The first WP-205-I2 implementation attempt embedded event dispatch into the approved runtime graph. Local verification produced lifecycle regressions, invalid test construction, PHPStan errors, and governance diagnostics. The implementation was rejected and completely removed. The WP-205-I1 baseline was restored and revalidated with 467 tests, 1238 assertions, zero PHPStan errors, zero Builder diagnostics, and deterministic artifact generation.

## 2. Corrective decision

The revised architecture in EG-214-A1 is acceptable for detailed design because it restores the required dependency direction:

- runtime execution is authoritative;
- observation is optional;
- dispatch failures are isolated;
- the default graph remains unchanged;
- integration is achieved by composition rather than embedded orchestration.

## 3. Mandatory design constraints

The next implementation proposal SHALL NOT modify:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- the runtime state machine;
- the historical capability list.

Any need to modify those elements invalidates the proposed increment and requires a new architecture review.

## 4. Required evidence before implementation

The detailed design must identify:

1. the exact public boundary to wrap;
2. every lifecycle event and its approved construction path;
3. the isolation mechanism for listener exceptions;
4. the immutable observation diagnostic model;
5. characterization tests proving unchanged default behavior;
6. a removal test proving the adapter has no residual effect.

## 5. Review outcome

**Status: Draft for Review.**

The corrective architecture is suitable to proceed to I2-B1 characterization and contract design after explicit approval. It does not authorize production runtime integration by itself.
