---
id: EG-373
title: Optional Parameters and Deterministic Route Precedence
summary: Specifies trailing optional path parameters, deterministic specificity ordering and closed-failure ambiguity detection for advanced routing.
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
  - optional-parameters
  - precedence
  - specification
depends_on:
  - EG-369
  - EG-370
  - EG-371
  - EG-372
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Optional Parameters and Deterministic Route Precedence

## Objective

WP-225 I5 introduces one trailing optional path segment and a deterministic specificity model without changing the public `RouteMatch` contract.

## Optional segments

An optional parameter SHALL occupy one trailing segment such as `/articles/{page?}`. Expansion SHALL produce ordinary required and omitted route variants before matching. Ambiguous or non-trailing optional segments SHALL be rejected during construction.

## Precedence

Matching SHALL prefer static segments, then constrained parameters, then longer required paths, then explicit priority, with stable lexical fallback. Registration order SHALL NOT silently override a more specific route.

## Ambiguity

Routes with indistinguishable effective method/path shapes SHALL fail closed before runtime matching.

## Compatibility

Existing `RouteDefinition`, `RouteMatcher` and `RouteMatch` semantics SHALL remain available. I5 SHALL NOT add route compilation or cache persistence.
