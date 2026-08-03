---
id: EG-374
title: Route Compilation Cache and Diagnostics
summary: Specifies deterministic route-table compilation, versioned JSON cache payloads, fingerprint verification and structured routing diagnostics.
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
  - compilation
  - cache
  - diagnostics
  - specification
depends_on:
  - EG-369
  - EG-370
  - EG-371
  - EG-372
  - EG-373
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Route Compilation Cache and Diagnostics

## Objective

WP-225 I6 compiles normalized routes into a deterministic table with a SHA-256 fingerprint, provides a versioned JSON cache format and reports closed-failure diagnostics without serializing executable runtime objects.

## Compilation

Compilation SHALL expand optional routes, verify deterministic precedence and produce a canonical fingerprint from route names, methods, paths, handlers, parameters and middleware. Ambiguity or malformed definitions SHALL produce structured diagnostics and no table.

## Cache

The cache SHALL contain only structured scalar and array data, a format version and the compiled fingerprint. Native object serialization, closures, controllers, services, requests and process resources SHALL NOT be stored. Decoding SHALL reconstruct route definitions and verify the fingerprint before use.

## Diagnostics

Diagnostics SHALL expose a stable code, safe message, severity and scalar metadata. Internal exception messages SHALL NOT be copied into cache or compilation diagnostics.

## Compatibility

The compiled matcher SHALL preserve `RouteMatch` semantics and may be adopted explicitly without removing the existing deterministic matcher.
