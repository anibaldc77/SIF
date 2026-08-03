---
id: EG-358
title: HTTP Context, Events, Logging and Error Responses
summary: "Specifies request-scoped execution context creation, lifecycle events, safe HTTP logging metadata, and deterministic error responses."
authors:
  - SIF Engineering
created: 2026-08-02
updated: 2026-08-02
tags:
  - http
  - context
  - events
  - logging
  - errors
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-223
depends_on:
  - EG-353
  - EG-354
  - EG-355
  - EG-356
  - EG-357
related_adrs: []
---

# HTTP Context, Events, Logging and Error Responses

## Purpose

Define the request-scoped coordination boundary that creates an explicit execution context, dispatches lifecycle events, records safe operational metadata, and converts routing failures or unexpected throwables into immutable HTTP responses.

## Requirements

- A root execution context is created for every handled request.
- The context is attached immutably to the request as `execution.context`.
- Start, completion and failure events are synchronous and optional.
- Logs contain method, path, status and opaque context identifiers only.
- Authorization headers, cookies, request bodies and query values are excluded from default logs.
- Route-not-found produces 404.
- Method-not-allowed produces 405 with an `Allow` header.
- Unexpected throwables are passed to the existing Error Handling subsystem and translated to a generic 500 response containing only the opaque failure identifier.
- The coordinator never emits the response and never terminates the process.
