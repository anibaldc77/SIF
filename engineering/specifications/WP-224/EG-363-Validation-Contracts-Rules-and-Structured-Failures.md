---
id: EG-363
title: Validation Contracts, Rules and Structured Failures
summary: Specifies deterministic controller-input validation through immutable schemas, explicit rules, source-aware paths and safe structured issues before action invocation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-224
tags:
  - controller
  - validation
  - rules
  - input
  - issues
  - specification
depends_on:
  - EG-361
  - EG-362
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Validation Contracts, Rules and Structured Failures

WP-224 I3 defines the deterministic validation boundary between resolved request input and controller-action invocation.

## Requirements

Validation SHALL operate on immutable `RequestInput` values and SHALL preserve source-aware paths such as `body.email` and `route.id`. Expected invalid input SHALL produce `ValidationIssue` values instead of escaping as generic exceptions.

A `ValidationSchema` SHALL contain explicitly declared fields and rules. Duplicate paths SHALL be rejected. A `Validator` SHALL evaluate fields without mutating input and SHALL return issues in stable path/code/message order.

## Initial rule set

The initial governed rules are required, nullable, scalar/array type, minimum, maximum, exact length, regular-expression pattern and membership in an explicit set. Null values declared nullable SHALL bypass subsequent rules. Missing required values SHALL remain distinguishable from explicit null.

## Security

Issues SHALL expose only code, path, safe message and scalar metadata. Input values, credentials, authorization headers, cookies and body fragments SHALL not be copied into issue metadata.

## Lifecycle

Validation SHALL complete before controller invocation. A failed `ValidationResult` SHALL prevent partial action execution. Domain invariants and persistence constraints remain outside this layer.

## Exclusions

I3 does not map validation issues to HTTP responses, negotiate content, invoke controllers or define Problem Details. Those concerns remain assigned to later WP-224 increments.
