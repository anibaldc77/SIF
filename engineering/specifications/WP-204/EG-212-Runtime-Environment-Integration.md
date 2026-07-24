---
id: EG-212
title: Runtime Environment Integration
summary: Integrates immutable environment variables with Application, Bootstrap, Runtime, lifecycle freezing and capability discovery.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - environment
  - runtime
  - bootstrap
  - lifecycle
work_package: WP-204
depends_on:
  - EG-210
  - EG-211
  - EG-209
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-212 — Runtime Environment Integration

## Purpose

Integrate environment variable providers into the SIF Runtime without replacing the legacy deployment `EnvironmentInterface` value object.

## Runtime model

`Bootstrap` builds a deterministic repository from native variables and an optional `.env` source. Dotenv values have precedence over native values. The same repository instance is owned by `Application` and exposed read-only by `Runtime`.

## Public integration

- `EnvironmentAwareApplicationInterface::variables()` exposes the mutable repository during boot.
- `RuntimeInterface::environment()` exposes the provider read API.
- the `environment` capability is registered by default;
- providers may read and mutate variables during `register()` and `boot()`;
- successful boot freezes the repository;
- failed boot leaves it mutable for diagnostics.

## Compatibility

`ApplicationInterface::environment()` remains unchanged and continues returning the legacy deployment environment (`development`, `testing`, `staging`, `production`, or custom).

## Acceptance criteria

1. Native and dotenv sources are merged deterministically.
2. Dotenv has precedence over native values.
3. Application and Runtime expose the same repository data.
4. Providers can access variables before freeze.
5. Successful boot freezes variables.
6. Failed boot does not freeze variables.
7. Capability discovery includes `environment`.
8. PHPUnit, PHPStan and Builder complete without diagnostics.
