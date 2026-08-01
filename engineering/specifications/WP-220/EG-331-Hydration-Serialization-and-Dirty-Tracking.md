---
id: EG-331
title: Hydration Serialization and Dirty Tracking
summary: Defines executable BaseModel 2.0 casts, controlled hydration, mass assignment, immutable original snapshots, deterministic dirty tracking and public versus storage serialization.
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
  - hydration
  - serialization
  - dirty-tracking
  - casts
depends_on:
  - EG-330
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-331 — Hydration, Serialization and Dirty Tracking

## 1. Purpose

WP-220 I3 establishes the in-memory attribute state used by BaseModel 2.0 before CRUD and repository integration are introduced.

## 2. Casting

`ModelAttributeCaster` SHALL execute the casts declared by metadata. Null SHALL be accepted only when the attribute is nullable. Integer and boolean conversions SHALL be strict enough to reject ambiguous input. JSON SHALL decode to an array and invalid JSON SHALL fail closed. Date-time values SHALL be normalized to `DateTime` or `DateTimeImmutable` according to metadata.

## 3. Hydration

Trusted hydration MAY assign read-only and non-fillable attributes because persisted records must reconstruct complete model state. Unknown attributes SHALL be rejected. Hydration SHALL establish both current values and an original snapshot.

## 4. Assignment

Mass assignment SHALL accept only attributes declared `fillable`. Direct assignment SHALL reject read-only attributes. All assignment paths SHALL execute declared casts and nullability validation.

## 5. Original state and dirty tracking

The state SHALL preserve an original snapshot independent from current mutable date-time objects. Dirty tracking SHALL compare all declared attributes deterministically and SHALL return only changed current values. `syncOriginal()` SHALL mark the current state as clean after a successful persistence boundary.

## 6. Serialization

Public serialization SHALL omit hidden attributes. Storage serialization SHALL include hidden attributes because visibility is an API concern, not a persistence filter. Date-time values SHALL serialize using ISO 8601 ATOM format. Neither mode SHALL include undefined attributes.

## 7. Deferred scope

I3 does not implement repositories, CRUD, query APIs, soft delete, lifecycle hooks, events, audit, relations or runtime registration.
