---
id: EG-379
title: Session Contracts State and Storage-Neutral Runtime
summary: Specifies the session identifier, record, request-scoped state, storage contract and storage-neutral runtime delivered by WP-226 I3.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
tags:
  - session
  - storage
  - runtime
  - security
  - specification
depends_on:
  - EG-378
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Session Contracts, State and Storage-Neutral Runtime

WP-226 I3 defines a storage-neutral session runtime. Session state SHALL remain request-scoped and SHALL NOT depend on PHP native sessions, PDO, Redis, filesystems or any concrete persistence technology.

## Identifier and record

`SessionId` SHALL validate opaque base64url-compatible identifiers and SHALL reject malformed external input. `SessionRecord` SHALL contain the identifier, structured session data, creation time, last-access time and a monotonic version value. Session payloads SHALL be represented as `array<string, mixed>` and SHALL NOT contain transport resources or process-local handles.

## Storage contract

`SessionStoreInterface` SHALL expose explicit read, write and delete operations. The contract SHALL be neutral with respect to locking, transactions and garbage collection; concrete adapters MAY add those capabilities later without changing the runtime boundary.

## Request-scoped state

`SessionState` SHALL provide explicit read, write, remove, destroy and regeneration operations. It SHALL track lifecycle intent without reading globals or persisting itself. Destroyed state SHALL not be written. Regeneration SHALL preserve logical data while replacing and invalidating the previous identifier.

## Runtime

`SessionRuntime` SHALL open a session from an optional candidate identifier, validate its format, load its record, enforce idle and absolute expiration and create a new state when no valid record exists. Expired records SHALL be deleted before replacement. Commit SHALL persist through `SessionStoreInterface`, delete destroyed sessions and invalidate prior identifiers during regeneration.

The runtime SHALL depend on `ClockInterface` and `SessionIdGeneratorInterface`. It SHALL NOT call the native session API, read cookies, emit headers or infer identifiers from query, route or body values.

## Product boundary

I3 does not parse incoming cookies, attach response cookies, provide lifecycle middleware, flash data or CSRF validation. Those concerns remain assigned to later WP-226 increments.
