---
id: EG-333
title: Model Query Pagination and Soft Delete
summary: Defines the immutable BaseModel query API, page results, explicit execution boundary and governed soft-delete lifecycle.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - foundation
  - basemodel
  - query
  - pagination
  - soft-delete
depends_on:
  - EG-332
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-333 — Model Query, Pagination and Soft Delete

## 1. Purpose

WP-220 I5 introduces a high-level immutable query API for BaseModel 2.0 and an explicit soft-delete service without placing SQL or hidden I/O inside model objects.

## 2. Query construction

`ModelQuery` SHALL validate model attributes through `ModelMetadata` and SHALL translate its state to the provider-neutral `Query` model. Query methods SHALL return new instances. Query construction SHALL NOT execute repository operations.

## 3. Execution boundary

`ModelQueryService` SHALL be the explicit execution boundary. It SHALL validate repository name and managed type before executing. Repository results SHALL be checked against the model class declared in metadata.

## 4. Pagination

`ModelPage` SHALL expose the selected page, page size, items and a conservative next-page indicator. I5 does not require an implicit count query.

## 5. Soft delete

Models with a declared `deletedAt` attribute SHALL exclude deleted rows by default. `withTrashed()` SHALL remove that scope and `onlyTrashed()` SHALL require non-null deleted timestamps. `ModelSoftDeleteManager` SHALL update the managed attribute and persist through `ModelRepositoryBridge`. Force deletion SHALL remain explicit.

## 6. Deferred scope

Lifecycle hooks, events, audit, relations and Unit of Work integration remain deferred to I6 and I7.
