---
id: EG-375
title: CLI Skeleton Integration and Routing Examples
summary: Specifies safe routing inspection commands and deterministic user-owned advanced-routing skeleton templates.
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
  - cli
  - skeleton
  - examples
  - specification
depends_on:
  - EG-369
  - EG-370
  - EG-371
  - EG-372
  - EG-373
  - EG-374
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# CLI Skeleton Integration and Routing Examples

## Objective

WP-225 I7 exposes compiled-route inspection through read-only CLI commands and generates deterministic advanced-routing example files without executing compilation, cache mutation or application code during skeleton generation.

## CLI

`route:list` SHALL expose route names, methods, paths, handlers, middleware, format version and fingerprint. `route:cache:inspect` SHALL expose only route count, format version and fingerprint. Both commands SHALL be inspection-only.

## Skeleton

The skeleton SHALL generate `config/routing.php` and `routes/advanced.php` as user-owned files using overwrite policy `fail`. Generated examples SHALL use explicit route groups, metadata and defaults and SHALL NOT perform filesystem scanning or runtime discovery.

## Safety

CLI output and templates SHALL NOT serialize closures, controllers, services, requests, contexts or secrets.
