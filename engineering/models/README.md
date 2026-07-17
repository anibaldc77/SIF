---
id: ENGINEERING-MODELS-README
title: Engineering Models
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-17
updated: 2026-07-17
tags:
  - engineering
  - models
  - domain
work_package: WP-100
depends_on:
  - ES-001
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# Engineering Models

Engineering models describe stable concepts shared across standards, schemas, templates and SIF Builder. They explain the domain without replacing normative requirements.

## Current Models

| Model | Purpose |
|---|---|
| `Metadata-Model.md` | Defines the engineering artifact metadata domain and its invariants. |

## Model Rules

- A model MAY explain concepts, relationships and invariants.
- A model SHALL identify the normative standards that govern it.
- A model SHALL NOT silently introduce requirements that are absent from controlling standards.
- Schemas and implementation code SHOULD use the terminology established by the model.
