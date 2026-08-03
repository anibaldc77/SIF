---
id: EG-371
title: Named Routes and URL Generation
summary: Specifies deterministic reverse routing by stable route name, validated path parameters, defaults, residual query values, fragments and explicitly trusted absolute base URIs.
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
  - named-routes
  - url-generation
  - specification
depends_on:
  - EG-369
  - EG-370
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Named Routes and URL Generation

## Objective

WP-225 I3 introduces deterministic reverse routing from stable `RouteName` identities. Generation SHALL not inspect the current request or forwarded headers.

## Parameters

Required path parameters SHALL be validated against the route constraint before percent encoding. Expanded-route defaults MAY satisfy missing parameters. Missing or invalid values SHALL produce structured generation failures.

## Relative and absolute URLs

Relative generation SHALL produce a path with optional residual query and fragment components. Absolute generation SHALL require an explicit trusted base URI containing scheme and host. Query encoding SHALL use RFC 3986 semantics.

## Compatibility

The implementation SHALL consume existing `RouteDefinition` instances and expanded definitions from EG-370. Matching, handler dispatch and middleware execution SHALL remain unchanged.

## Non-goals

I3 SHALL NOT introduce optional path segments, host parameters, route compilation, request-derived authority inference or automatic redirects.
