---
id: EG-278
title: Handlers, Filtering, Routing and Isolated Failure Reporting
summary: Defines provider-neutral handler contracts, deterministic filters and routes, immutable dispatch reports, and non-recursive emergency failure isolation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-213
tags:
  - logging
  - handlers
  - filtering
  - routing
  - failure-isolation
depends_on:
  - EG-273
  - EG-274
  - EG-275
  - EG-276
  - EG-277
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-278 — Handlers, Filtering, Routing and Isolated Failure Reporting

- Work Package: WP-213
- Increment: I6
- Status: Implemented
- Date: 2026-07-28

## Purpose

Define the additive dispatch boundary that sends immutable records to provider-neutral handlers through deterministic filters and named routes while isolating sink failures from application execution and from other routes.

## Decisions

1. Handlers consume an already-created immutable `LogRecord` and return no provider-specific result.
2. Filters are explicit predicates over records and do not mutate them.
3. Minimum-level filtering uses the canonical priorities of `LogLevel`.
4. Channel filtering matches exact canonical channel values.
5. Composite filters use logical AND and preserve declaration order.
6. Routes are named by portable lowercase identifiers and route names are unique within a router.
7. Routing preserves declaration order and evaluates every route independently.
8. A handler failure is captured as `LogHandlerFailure`; later routes continue.
9. Emergency reporting receives the route, record and original throwable.
10. Emergency-reporter failures are swallowed at the terminal boundary to prevent recursion.
11. Dispatch returns an immutable report of handled, filtered and failed routes.
12. No filesystem, network or vendor logger dependency is introduced.

## Compatibility

The increment is additive. It does not register handlers globally and does not modify `Application`, `Bootstrap`, the container or existing runtime behavior.
