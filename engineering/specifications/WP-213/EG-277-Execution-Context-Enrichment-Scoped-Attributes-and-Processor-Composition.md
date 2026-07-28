---
id: EG-277
title: Execution-Context Enrichment, Scoped Attributes and Processor Composition
summary: Defines explicit execution-context projection, immutable attribute scopes, and reusable deterministic processor compositions.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-213
tags:
  - logging
  - execution-context
  - scoped-attributes
  - processors
depends_on:
  - EG-273
  - EG-274
  - EG-275
  - EG-276
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-277 — Execution-Context Enrichment, Scoped Attributes and Processor Composition

- Work Package: WP-213
- Increment: I5
- Status: Implemented
- Date: 2026-07-28

## Purpose

Define the additive logging boundary that projects the public execution-context contract into structured records without coupling logging to context internals, introduces explicit attribute scopes, and permits reusable deterministic processor compositions.

## Decisions

1. Execution context is projected under the single top-level key `execution_context`.
2. Existing record attributes win unless overwrite is explicitly enabled.
3. Null optional context fields are omitted.
4. Custom context attributes may be excluded by policy at construction time.
5. Scoped attributes use portable lowercase identifiers and remain immutable.
6. Processor compositions are named, immutable, ordered, and reuse the failure semantics of `LogRecordProcessorPipeline`.
7. No ambient/global context lookup is introduced.
8. No arbitrary object inspection, hidden mutation, or provider-specific dependency is introduced.

## Context projection

The stable projection includes context, correlation, causation and parent identifiers; actor and tenant identifiers; operation, source, locale and timezone; creation timestamp; and optionally custom context attributes.

## Compatibility

The increment is additive. Existing logging records, factories, renderers and pipelines retain their public behavior.
