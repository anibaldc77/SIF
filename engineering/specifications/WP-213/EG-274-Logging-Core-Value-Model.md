---
id: EG-274
title: Structured Logging Core Value Model
summary: Defines immutable levels, channels, message templates, timestamps, clock contracts, throwable metadata, records, and the initial logging failure taxonomy.
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
  - foundation
  - logging
  - value-objects
  - clock
  - exceptions
depends_on:
  - EG-273
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-274 — Structured Logging Core Value Model

## 1. Purpose

WP-213-I2 establishes the dependency-free core value model for Structured Logging 2.0. It introduces no handlers, processors, routing, normalization pipeline, configuration loading, or runtime integration.

## 2. Normative types

The increment SHALL provide immutable types for `LogLevel`, `LogChannel`, `LogMessage`, `LogTimestamp`, `ThrowableMetadata`, and `LogRecord`, plus `ClockInterface`, `SystemClock`, and `FrozenClock`.

## 3. Levels

The canonical ordered set is `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, and `emergency`. Comparison SHALL use stable integer priorities and SHALL reject unknown values.

## 4. Channels

Channels SHALL use portable lowercase identifiers beginning with an alphanumeric letter. Dot, underscore, and hyphen separators are permitted only between non-empty alphanumeric segments.

## 5. Messages

Messages SHALL preserve the original template and expose unique placeholders in first-appearance order. Empty templates SHALL be rejected. Rendering is outside I2.

## 6. Time boundary

All timestamps SHALL be canonicalized to UTC with microsecond precision. Production time SHALL be obtained through `ClockInterface`; deterministic tests SHALL use `FrozenClock`.

## 7. Throwable metadata

Throwable projection SHALL be explicit and bounded. I2 includes only type, message, and code. Stack traces, object state, arguments, environment data, and previous exceptions SHALL NOT be serialized by the core value object.

## 8. Records

A record SHALL contain timestamp, level, channel, message, structured attributes, optional throwable metadata, and an optional record identifier. Before the later normalization boundary exists, attributes SHALL contain only recursively structured arrays and scalar or null values. Arbitrary objects and resources SHALL be rejected.

## 9. Failure taxonomy

All logging-specific failures SHALL derive from `LoggingException`. I2 defines specialized invalid-level, invalid-channel, and invalid-record failures. Delivery and isolation failures remain deferred.

## 10. Compatibility

All additions are isolated under `Sif\\Foundation\\Logging`. No existing public constructor or runtime path changes in I2.
