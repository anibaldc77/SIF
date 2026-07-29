---
id: EG-293
title: Module Resource Contributions and Override Planning
summary: Defines explicit module resource contributions, governed override policies, deterministic precedence and immutable compiled contribution plans.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-215
tags:
  - foundation
  - resources
  - modules
  - overrides
  - planning
depends_on:
  - EG-289
  - EG-290
  - EG-291
  - EG-292
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-293 — Module Resource Contributions and Override Planning

## 1. Purpose

WP-215-I5 defines how enabled modules contribute logical resources without mutating the resource registry implicitly or relying on discovery order.

The increment SHALL compile an explicit ordered list of module contributions into an immutable effective plan. It SHALL NOT resolve filesystem paths, publish resources, load translations or integrate with runtime bootstrap.

## 2. Contribution model

A `ModuleResourceContribution` SHALL associate:

- one existing `ModuleId`;
- one immutable `ResourceDescriptor`;
- one explicit `ResourceOverridePolicy`.

The contribution order SHALL be derived solely from the input sequence supplied to the planner and SHALL be represented by a non-negative integer.

## 3. Override policies

The governed policies are:

- `forbid`: any collision is rejected;
- `replace_if_higher_priority`: replacement is allowed only when the candidate priority is strictly greater;
- `replace_always`: replacement is explicit regardless of priority.

Equal priority SHALL NOT satisfy `replace_if_higher_priority`. No implicit last-write-wins behavior is permitted.

## 4. Collision identity

Two contributions collide only when their resource descriptors have the same case-sensitive qualified identifier:

```text
<namespace>:<identifier>
```

Differences in type, source, metadata, owner or module identity SHALL NOT avoid a collision.

## 5. Deterministic plan

The planner SHALL process contributions in input order. Each accepted override SHALL replace the current effective contribution and produce an immutable `ResourceOverrideDecision` retaining both winner and replaced contribution.

The final effective contributions SHALL be ordered by:

1. descending resource priority;
2. ascending original contribution order.

The compiled plan SHALL expose effective resources and override decisions without mutable registration methods.

## 6. Failure model

Typed failures SHALL represent:

- unknown override policy;
- invalid contribution order;
- forbidden collision;
- insufficient priority for a conditional override.

A failed compilation SHALL not return a partial plan.

## 7. Compatibility and boundaries

The increment is additive. It reuses `ModuleId` from WP-212 and the resource value model from WP-215-I2. It SHALL NOT modify module lifecycle contracts, registry behavior, filesystem resolution or public runtime signatures.
