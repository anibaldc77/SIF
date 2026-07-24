---
id: ES-003
title: Document Class Model
summary: This Standard defines the document classes used to select structural and validation behavior for governed SIF engineering artifacts. Category describes what an artifact is; document class describes how it is governed and validated.
status: Draft for Review
version: 0.1.0
category: Engineering Standard
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-17
updated: 2026-07-17
tags:
  - documentation
  - document-class
  - validation
  - builder
work_package: WP-100
depends_on:
  - ES-001
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# ES-003 — Document Class Model

## Executive Summary

This Standard defines the document classes used to select structural and validation behavior for governed SIF engineering artifacts. Category describes what an artifact is; document class describes how it is governed and validated.

This document is normative.

## 1. Purpose

ES-003 establishes:

- the canonical document class enumeration;
- the responsibilities of each class;
- default category-to-class mappings;
- class-specific conformance rules;
- Builder validation requirements.

## 2. Canonical Classes

The allowed values are:

- `NormativeDocument`
- `GovernanceDocument`
- `ReviewDocument`
- `InformativeDocument`
- `TemplateDocument`

Values are case-sensitive.

## 3. Class Definitions

### 3.1 NormativeDocument

Defines requirements, contracts or specifications that conforming artifacts and implementations SHALL satisfy.

A Normative Document SHALL:

- declare normative authority and scope;
- use RFC 2119-style requirement keywords consistently;
- define conformance or acceptance criteria;
- identify normative dependencies.

Default categories:

- Constitution;
- Architecture Specification;
- Engineering Standard;
- Normative Specification.

### 3.2 GovernanceDocument

Controls engineering decisions, processes, lifecycle or policy.

A Governance Document SHALL:

- identify the governed process or decision;
- state authority, applicability and lifecycle;
- preserve rationale where decisions are recorded.

Default categories:

- Policy;
- Architecture Decision Record;
- Request for Comments;
- Work Package.

### 3.3 ReviewDocument

Records verification evidence, findings and disposition against explicit criteria.

A Review Document SHALL:

- identify the reviewed artifact or implementation;
- state review criteria and evidence;
- record findings, risks and disposition;
- distinguish observations from blocking findings.

Default categories:

- Architecture Review;
- Implementation Review.

### 3.4 InformativeDocument

Explains, navigates or illustrates the engineering system without creating new normative requirements.

An Informative Document SHALL NOT use mandatory language to create obligations absent from a controlling normative artifact.

Default category:

- Informative Document.

### 3.5 TemplateDocument

Defines the canonical structure from which governed artifacts are created.

A Template Document SHALL:

- identify its target category or class;
- include required metadata placeholders;
- distinguish instructions from emitted artifact content;
- track the standard version it implements.

Default category:

- Template.

## 4. Category and Class Rules

### DC-001 — Required Class

Every governed artifact SHALL declare `document_class` after ES-003 reaches Approved status.

### DC-002 — Registered Value

`document_class` SHALL use one canonical class value.

### DC-003 — Semantic Separation

`category` SHALL identify artifact function. `document_class` SHALL identify governance and validation behavior. They SHALL NOT be treated as aliases.

### DC-004 — Default Mapping

Builder SHALL apply the following compatibility mapping:

| Category | Allowed default class |
|---|---|
| Constitution | `NormativeDocument` |
| Architecture Specification | `NormativeDocument` |
| Engineering Standard | `NormativeDocument` |
| Policy | `GovernanceDocument` |
| Architecture Decision Record | `GovernanceDocument` |
| Request for Comments | `GovernanceDocument` |
| Work Package | `GovernanceDocument` |
| Normative Specification | `NormativeDocument` |
| Architecture Review | `ReviewDocument` |
| Implementation Review | `ReviewDocument` |
| Informative Document | `InformativeDocument` |
| Template | `TemplateDocument` |

A non-default mapping requires an explicit approved exception defined by policy or ADR.

### DC-005 — No Requirement Escalation

Informative and Template documents SHALL NOT create normative requirements by themselves.

### DC-006 — Validation Composition

Builder validation SHALL compose:

1. Core Metadata validation from ES-002;
2. document-class validation from this Standard;
3. category-specific validation where defined;
4. repository-wide relationship validation.

## 5. Builder Model

Builder SHOULD expose the document class as a closed value object or enum. Unknown classes SHALL produce a validation error and SHALL NOT silently fall back to another class.

Class validators SHOULD be independently testable and SHALL return stable diagnostic codes.

## 6. Compatibility

Adding a new document class is a schema and Builder compatibility event. It requires:

- an approved ES-003 revision;
- an ES-002 compatibility review;
- metadata schema update;
- template updates;
- Builder implementation and tests;
- migration guidance for affected artifacts.

## 7. Conformance

An artifact conforms to ES-003 when:

- it declares a registered document class;
- its category and class are compatible;
- it satisfies the applicable class requirements;
- it passes Builder validation once such validation is available.

## 8. Rule Index

| Rule | Requirement |
|---|---|
| DC-001 | Governed artifacts declare a document class. |
| DC-002 | Classes use canonical values. |
| DC-003 | Category and class remain semantically distinct. |
| DC-004 | Category/class mappings are validated. |
| DC-005 | Informative and Template documents do not create obligations. |
| DC-006 | Builder composes validation layers. |
