---
id: EG-357
title: Middleware Pipeline and Handler Dispatch
summary: "Specifies explicit HTTP handler and middleware resolution, deterministic pipeline ordering, route-scoped request enrichment, short-circuit responses, and safe terminal dispatch."
authors:
  - SIF Engineering
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
created: 2026-08-02
updated: 2026-08-02
tags:
  - http
  - middleware
  - dispatch
work_package: WP-223
depends_on:
  - EG-353
  - EG-354
  - EG-355
  - EG-356
related_adrs:
  - ADR-0005
---

# Middleware Pipeline and Handler Dispatch

## Purpose

Define explicit HTTP handler and middleware contracts, deterministic registries, a single-pass middleware pipeline, and route-aware dispatch without coupling routing to controller construction.

## Requirements

- Handlers and middleware SHALL be resolved through explicit identifiers.
- Global middleware SHALL execute before route middleware.
- Route parameters SHALL be exposed as immutable request attributes.
- Middleware MAY short-circuit by returning a response.
- A middleware next-handler SHALL NOT be invoked more than once.
- Routing SHALL NOT instantiate or execute handlers.
- Dispatch SHALL reject unmatched route results.
- Registries SHALL reject duplicate identifiers and unknown components.

## Exclusions

This increment does not define exception-to-response translation, request context creation, events, logging, or the final HTTP kernel.
