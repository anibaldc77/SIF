---
id: EG-372
title: Host, Scheme and Port Constraints
summary: Specifies explicit transport constraints for advanced routes, including case-insensitive static or parameterized hosts, trusted schemes, effective ports and deterministic constrained matching.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-225
tags:
  - routing
  - host
  - scheme
  - port
  - specification
depends_on:
  - EG-369
  - EG-370
  - EG-371
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Host, Scheme and Port Constraints

## Objective

WP-225 I4 introduces explicit host, scheme and port constraints without trusting forwarded headers or changing the basic route model.

## Matching

Transport constraints SHALL be evaluated before path and method selection. Host comparison SHALL be case-insensitive. Host placeholders MAY contribute parameters to a successful `RouteMatch`. Scheme constraints SHALL initially support only `http` and `https`. Port matching SHALL account for implicit ports 80 and 443.

## Semantics

A transport mismatch SHALL produce `not-found`. A method mismatch after transport and path matching SHALL preserve `method-not-allowed`. Routes without transport constraints SHALL remain compatible.

## Security

The matcher SHALL use only explicitly supplied request-target data. `Forwarded` and `X-Forwarded-*` headers SHALL remain outside this subsystem.

## Non-goals

I4 SHALL NOT add proxy trust, redirects, route compilation, optional path segments or automatic authority inference.
