---
id: EG-034-A1
title: Deterministic Object Comparison Correction
summary: ReferenceIntegrityInspectorTest compared the findings produced by two independent inspector executions with PHPUnit assertSame().
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - deterministic
  - object
  - comparison
  - correction
work_package: WP-106
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-034-A1 — Deterministic Object Comparison Correction

## Status

Approved hotfix.

## Problem

`ReferenceIntegrityInspectorTest` compared the findings produced by two independent inspector executions with PHPUnit `assertSame()`.

The inspector correctly creates new immutable `ReferenceIntegrityFinding` instances on every invocation. Although both result sets contain equal values in the same deterministic order, they are not the same object instances. Consequently, strict identity comparison fails.

## Decision

Replace `assertSame()` with `assertEquals()` for the repeated inspection result.

This verifies structural equality of the findings—including code, severity, message, source, context, remediation, count, and ordering—without requiring object identity across independent executions.

## Scope

Test-only correction. No production behavior, public API, diagnostic code, analyzer registration, or execution policy changes.
