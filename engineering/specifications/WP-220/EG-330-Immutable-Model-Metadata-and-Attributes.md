---
id: EG-330
title: Immutable Model Metadata and Attributes
summary: Defines immutable BaseModel 2.0 metadata, attribute declarations, casts, mass-assignment visibility, simple and composite identities, managed timestamps and deterministic metadata registration.
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
  - metadata
  - attributes
  - identity
  - casts
  - compatibility
depends_on:
  - EG-329
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-330 — Immutable Model Metadata and Attributes

## 1. Purpose

WP-220 I2 establishes the immutable metadata vocabulary used by BaseModel 2.0 before hydration, serialization, persistence or lifecycle behavior is introduced.

Metadata SHALL be explicit, deterministic and independent of PDO. A model class SHALL NOT infer unrestricted storage behavior from public properties or dynamic properties.

## 2. Attribute names

`ModelAttributeName` SHALL accept PHP-compatible logical attribute names composed of letters, numbers and underscores, beginning with a letter or underscore.

Attribute names SHALL NOT contain SQL fragments, whitespace, punctuation or path expressions.

## 3. Attribute definitions

Each `ModelAttributeDefinition` SHALL declare:

- logical name;
- cast;
- nullability;
- fillable status;
- hidden status;
- read-only status.

An attribute SHALL NOT be both fillable and read-only.

Supported initial casts are mixed, string, integer, float, boolean, array, JSON, mutable date-time and immutable date-time.

I2 defines declarations only. Cast execution belongs to I3.

## 4. Identity

`ModelIdentityDefinition` SHALL contain one or more unique attributes and SHALL preserve declaration order.

One attribute represents a simple identity. More than one attribute represents a composite identity.

Every identity attribute SHALL exist in the enclosing metadata.

## 5. Model metadata

`ModelMetadata` SHALL bind:

- a concrete model class;
- a logical repository name;
- an immutable attribute map;
- an identity definition;
- optional created, updated and deleted timestamp attributes.

Managed timestamp attributes SHALL exist in the attribute map. A deleted timestamp declares soft-delete capability but does not implement soft-delete behavior until I5.

## 6. Registration

`ModelMetadataRegistry` SHALL register at most one metadata instance per model class and SHALL resolve metadata deterministically by fully-qualified class name.

Registration SHALL NOT instantiate models, resolve repositories, inspect the database or execute queries.

## 7. Security and compatibility

Metadata summaries SHALL describe policy but SHALL NOT contain attribute values.

Legacy dynamic-property behavior is not reproduced. Existing applications SHALL migrate fillable, hidden, casts and key declarations into explicit metadata.

## 8. Deferred scope

I2 does not implement:

- attribute storage;
- cast execution;
- hydration;
- serialization;
- dirty tracking;
- CRUD;
- query APIs;
- lifecycle events;
- relationships;
- runtime registration.
