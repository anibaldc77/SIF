---
id: EG-275
title: Bounded Normalization, Canonical Serialization and Secret Redaction
summary: Defines the deterministic and secret-safe boundary that converts arbitrary logging inputs into bounded canonical structured values.
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
  - normalization
  - redaction
  - canonical-serialization
depends_on:
  - EG-273
  - EG-274
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-275 — Bounded Normalization, Canonical Serialization and Secret Redaction

## Purpose

This increment establishes the governed boundary between arbitrary application values and immutable structured logging attributes.

## Normalization contract

Normalization is deterministic, dependency-free, and bounded by an immutable policy. The policy controls maximum depth, collection size, string length, and explicit markers for truncation, depth exhaustion, and unsupported values.

Supported projections include scalars, arrays, dates, enums, throwables, stringable values, generic objects, and resources. Generic objects are represented only by class identity. Their properties are never inspected. Throwable projection excludes stack traces and object state.

## Redaction contract

Redaction occurs after normalization and before serialization or handler delivery. Sensitive keys are matched case-insensitively after canonical separator normalization. The complete value under a sensitive key is replaced by an explicit marker. Partial disclosure is not permitted.

Default protected keys include passwords, tokens, authorization headers, API keys, private keys, client secrets, cookies, and session identifiers. Applications may supply an immutable custom policy.

## Canonical serialization

Canonical JSON serialization recursively sorts map keys while preserving list order. It uses UTF-8 JSON, unescaped Unicode and slashes, exception-based failure handling, and preservation of zero-fraction floats.

Canonical output is suitable for deterministic comparisons and future fingerprints. It is not a persistence format guarantee.

## Security invariants

- No reflection or arbitrary object-property traversal.
- No stack trace serialization.
- No secret-value substring exposure.
- No unbounded depth, collection size, or string length.
- No handler or runtime integration in this increment.

## Deferred scope

Record factories, processors, enrichment, rendering, handlers, routing, logging plans, diagnostics, module contributions, and runtime integration remain deferred.
