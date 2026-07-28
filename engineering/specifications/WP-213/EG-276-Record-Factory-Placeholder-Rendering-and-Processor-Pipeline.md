---
id: EG-276
title: Record Factory, Placeholder Rendering and Processor Pipeline
summary: Defines deterministic construction, safe message rendering, and ordered processing of immutable structured log records.
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
  - record-factory
  - rendering
  - processors
depends_on:
  - EG-273
  - EG-274
  - EG-275
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-276 — Record Factory, Placeholder Rendering and Processor Pipeline

## Purpose

This increment establishes the deterministic path from raw logging inputs to immutable records, safely rendered messages, and an ordered processor pipeline.

## Record construction

`LogRecordFactoryInterface` accepts a level, channel, message template, raw attributes, optional throwable, and optional record identifier. The implementation obtains time only through `ClockInterface`, normalizes and redacts attributes before record construction, and projects throwables through `ThrowableMetadata`.

Attribute keys must be non-empty strings. The factory never mutates input values and never exposes raw values after normalization.

## Placeholder rendering

Rendering operates only on immutable normalized record attributes. Placeholders support exact keys and dotted paths. Scalar values use deterministic textual forms; arrays use canonical structured serialization. Missing placeholders remain visible in the rendered text and are reported separately rather than silently removed.

Rendering never reads object properties, exception traces, environment variables, or external state. Redacted values remain redacted because rendering occurs after record construction.

## Processor pipeline

Processors implement a single immutable transformation contract from one `LogRecord` to another. The pipeline executes processors strictly in declaration order. An empty pipeline returns the original record instance.

Processor failures are fail-fast and wrapped in a typed exception that records the zero-based position and processor class while preserving the original throwable as the cause. Handler isolation and emergency reporting remain separate later responsibilities.

## Attribute enrichment

The initial built-in processor adds deterministic normalized attributes. By default, existing record attributes win. An explicit overwrite mode allows the processor contribution to replace existing keys. Timestamp, level, channel, message, throwable metadata, and record identifier are preserved.

## Deferred scope

Execution-context enrichment, handler delivery, routing, filters, recursion guards, emergency reporting, immutable logging plans, module contributions, configuration binding, and runtime integration remain deferred.
