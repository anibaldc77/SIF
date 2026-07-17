---
id: MODEL-METADATA
title: Engineering Metadata Model
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-17
updated: 2026-07-17
tags:
  - metadata
  - domain-model
  - builder
work_package: WP-100
depends_on:
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# Engineering Metadata Model

## 1. Purpose

This model describes the domain concepts used to identify, classify, version, relate and validate governed SIF engineering artifacts. ES-002 remains the normative authority.

## 2. Aggregate Boundary

`EngineeringMetadata` is the aggregate root for the metadata attached to one governed artifact.

It owns:

- canonical identity;
- human-readable title;
- lifecycle status;
- semantic version;
- artifact category and document class;
- authorship and dates;
- classification tags;
- Work Package ownership;
- dependencies and ADR relationships;
- supersession relationships.

The body of the Markdown document is outside this aggregate. Repository-wide uniqueness and graph validation are also outside the aggregate and belong to `MetadataRegistry` or equivalent repository services.

## 3. Core Concepts

### 3.1 Canonical Identifier

A permanent value that identifies one artifact across revisions, paths and generated indexes.

### 3.2 Lifecycle Status

The controlled state of the artifact in the engineering lifecycle. Status transitions are governed by standards and policies rather than inferred from Git history.

### 3.3 Document Version

A Semantic Versioning value representing the evolution of the artifact itself. It is independent from the SIF product version and the Git commit identifier.

### 3.4 Artifact Category

The functional kind of engineering artifact, such as Engineering Standard, Work Package or Architecture Decision Record.

### 3.5 Document Class

The validation behavior assigned to an artifact. A document class determines structural expectations without changing the artifact category.

### 3.6 Reference

A canonical identifier that creates a typed relationship between artifacts. Core relationship types are dependency, related ADR, supersedes and superseded by.

## 4. Invariants

Within one `EngineeringMetadata` instance:

1. required fields are present and non-empty;
2. the canonical identifier follows ES-002 formatting rules;
3. the version is valid Semantic Versioning;
4. status, category and document class use registered values;
5. authors contain at least one unique non-empty value;
6. tags and references contain no duplicates;
7. a Release Candidate uses an `-rc.N` version;
8. an Approved artifact uses a stable version;
9. a Superseded artifact identifies `superseded_by`;
10. an artifact does not supersede itself or depend on itself.

Repository services enforce:

- global identifier uniqueness;
- referenced artifact existence;
- dependency-cycle detection;
- reciprocal supersession consistency;
- chronological consistency between `created` and `updated`;
- compatibility between category and document class.

## 5. Conceptual Components

```text
EngineeringMetadata
├── ArtifactId
├── DocumentTitle
├── LifecycleStatus
├── DocumentVersion
├── ArtifactCategory
├── DocumentClass
├── Authors
├── Dates
├── Tags
├── WorkPackageReference
├── DependencyReferences
├── ArchitectureDecisionReferences
└── Supersession
```

## 6. Processing Lifecycle

```text
Markdown Document
      ↓
Front Matter Reader
      ↓
Raw Metadata Mapping
      ↓
Schema Validation
      ↓
Domain Validation
      ↓
EngineeringMetadata
      ↓
Registry / Index / Generator
```

Syntax validation occurs before domain construction. Repository-wide validation occurs after individual metadata instances have been registered.

## 7. Error Model

A validation result SHOULD collect all independent errors rather than stopping at the first failure. Every error SHOULD expose:

- a stable rule or error code;
- the affected metadata path;
- a human-readable message;
- the invalid value when safe to expose.

## 8. Evolution

New optional fields may be added compatibly. New required fields, changed enumerations or changed semantics require a versioned update to ES-002 and coordinated updates to schemas, Builder rules, templates and tests.
