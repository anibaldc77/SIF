---
id: EG-355
title: Immutable HTTP Response and Native Transport Adapters
summary: Defines immutable HTTP response values and the native request and response transport boundary for the SIF HTTP Foundation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-223
tags:
  - foundation
  - http
  - response
  - transport
  - sapi
  - value-model
depends_on:
  - EG-353
  - EG-354
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Immutable HTTP Response and Native Transport Adapters

WP-223 I3 defines immutable HTTP response values and isolates native process access behind explicit transport adapters.

## Requirements

- Response status, protocol, headers and body MUST be immutable.
- Status codes MUST be validated within the HTTP range and reason phrases MUST reject CR, LF and NUL.
- Response body metadata MUST be declarative and MUST NOT own PHP streams.
- Content-Type and Content-Length MAY be derived during normalization when not explicitly supplied.
- Response emission MUST remain separate from response construction.
- A native emitter MUST emit at most once and MUST preserve multiple header field values.
- Informational responses, 204 and 304 responses MUST NOT emit body bytes.
- Native request creation MUST be available from explicit arrays for deterministic tests.
- Direct access to globals and `php://input` MUST be confined to the native request adapter.
- Uploaded-file native structures MUST be converted into inert descriptors defined by I2.
- Native response emission MUST be replaceable with callbacks so status, headers and body can be verified independently.

## Product boundary

This increment does not route requests, execute middleware, resolve handlers, translate exceptions or create request-scoped execution contexts. Those concerns remain assigned to later WP-223 increments.
