---
id: EG-354
title: Immutable HTTP Request URI Headers and Body Value Model
summary: Defines the immutable provider-neutral HTTP request value model used by the SIF HTTP Foundation.
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
  - request
  - uri
  - headers
  - value-model
depends_on:
  - EG-353
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Immutable HTTP Request URI Headers and Body Value Model

WP-223 I2 defines immutable HTTP request values without reading process globals or binding the Foundation layer to a concrete server API.

## Requirements

- Request values MUST be immutable after construction.
- Supported HTTP methods and protocol versions MUST be validated explicitly.
- URI components MUST be represented independently and serialized deterministically.
- Header lookup MUST be case-insensitive while preserving the first declared field name for output.
- Header values MUST reject CR, LF and NUL characters.
- Request bodies MUST contain bytes and declarative media metadata only; they MUST NOT own PHP streams.
- Uploaded files MUST be inert descriptors and MUST NOT move, open or delete files.
- Query, cookie, server and request-attribute collections MUST be independent immutable bags.
- Request mutation methods MUST return new request instances.
- No component in this increment may read `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES` or `php://input`.

## Product boundary

This increment supplies the value model required by later transport adapters, routing, middleware and dispatch. Response creation, native SAPI adaptation, routing and middleware remain outside I2.
