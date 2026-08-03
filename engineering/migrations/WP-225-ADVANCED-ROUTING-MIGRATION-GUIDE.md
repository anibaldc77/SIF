---
id: WP-225-ADVANCED-ROUTING-MIGRATION-GUIDE
title: WP-225 Advanced Routing Migration Guide
summary: Provides an incremental path from basic WP-223 routing to grouped, named, constrained and compiled WP-225 routing without requiring a full application rewrite.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-225
tags:
  - routing
  - migration
  - compatibility
depends_on:
  - EG-376
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-225 Advanced Routing Migration Guide

## Starting point

A WP-223 application can keep `RouteDefinition`, `RouteRegistry` and `RouteMatcher` unchanged. WP-225 is optional and can be adopted capability by capability.

## Recommended sequence

1. Group related routes with `RouteGroup` and expand them before registration.
2. Build `NamedRouteIndex` only for routes that require reverse generation.
3. Supply `RouteBaseUri` explicitly when absolute URLs are required.
4. Introduce `ConstrainedRouteMatcher` only where host, scheme or port constraints are needed.
5. Replace ambiguous optional variants with `OptionalRouteDefinition` and validate precedence before deployment.
6. Compile stable route sets with `RouteCompiler` and inspect diagnostics.
7. Enable cache only after a successful compile and persist only `RouteCacheSerializer` output.
8. Use `route:list` and `route:cache:inspect` for safe inspection.

## Rollback

Because the original route definitions remain valid, an application can disable the compiled matcher or cache and return to the basic matcher without changing controller handlers or middleware identifiers.

## Prohibited migration shortcuts

Do not infer a trusted base URI from forwarding headers, serialize route objects natively, store closures in route cache, resolve route ambiguity by registration order or overwrite user-owned skeleton route files.
