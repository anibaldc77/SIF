---
id: EG-366
title: Exception Mapping and Problem Details
summary: Specifies deterministic controller exception mapping, RFC 9457-compatible Problem Details responses, safe structured validation errors and failure reporting without disclosure of internal exception data.
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
  - problem-details
  - exception
  - error-handling
  - api
  - specification
depends_on:
  - EG-361
  - EG-362
  - EG-363
  - EG-364
  - EG-365
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Exception Mapping and Problem Details

WP-224 I6 defines the controller-layer boundary that converts expected input failures and mapped exceptions into safe `application/problem+json` responses.

## Problem Details

A problem response SHALL contain `type`, `title` and `status`, and MAY contain `detail`, `instance` and safe extension members. Status values SHALL be HTTP error statuses. Extension members SHALL contain structured scalar data only and SHALL NOT overwrite reserved members.

Problem serialization SHALL be deterministic. Stack traces, filesystem paths, credentials, authorization headers, cookies and raw request bodies SHALL NOT be exposed.

## Structured input failures

Argument-resolution failures SHALL produce status `400` and SHALL preserve safe issue fields including code, source-derived path, message and safe metadata. Validation failures SHALL produce status `422` and SHALL preserve deterministic validation issue ordering.

Controller actions SHALL NOT execute when either argument resolution or validation has failed.

## Exception mappings

Mappings SHALL be explicit and registry-based. A mapping SHALL bind a throwable class to status, type, title and a safe fixed detail. Throwable messages SHALL NOT be exposed through mapped responses.

Duplicate mapping registrations SHALL be rejected. Exact class mappings SHALL take precedence over broader parent mappings.

## Unexpected failures

Unexpected throwables SHALL produce a generic status `500` response. When an `ErrorHandlerInterface` is composed, the throwable SHALL be reported through the existing error-handling subsystem and the response MAY expose only the opaque failure identifier.

The controller exception handler SHALL operate inside the HTTP/controller boundary and SHALL return a `ResponseInterface`; it SHALL NOT emit the response or terminate the process.
