---
id: EG-034
title: Reference Integrity Analyzer
summary: Defines the built-in analyzer that reports demonstrable integrity problems in resolved repository references.
document_class: Engineering Specification
category: Work Package
status: Approved
version: 1.0.0
work_package: WP-106
tags:
  - builder
  - analyzer
  - references
references:
  - EG-032
  - EG-009
  - EG-010
---

# EG-034 — Reference Integrity Analyzer

## 1. Purpose

`reference.integrity` validates the integrity characteristics that can be established from `RepositoryIndex` and `ResolutionResult` without reading or modifying source documents.

## 2. Preconditions

The analyzer requires:

- a `RepositoryWorkspace`;
- a `RepositoryIndex`;
- a `ResolutionResult`.

Missing inputs produce `ANALYZER-102` with error severity.

## 3. Functional diagnostics

| Code | Severity | Condition |
|---|---|---|
| `REFINT-201` | Error | A resolved workflow contains a broken target reference. |
| `REFINT-202` | Warning | The resolved graph contains a cycle. |
| `REFINT-203` | Warning | A document references itself. |
| `REFINT-204` | Warning | The same source, target and reference type occur more than once. |

Diagnostic codes follow the Engine format: one uppercase family followed by a hyphen and three digits.

## 4. Explicit exclusions

Ambiguous resolution is excluded because the current WP-102 model does not expose candidate sets or ambiguity states. It must not be inferred from missing or broken references.

## 5. Determinism

Findings are sorted by code, source identifier, source path and message. Cycle detection reuses the canonical cycle representation from WP-102.

## 6. Integration

The default CLI composition registers the analyzer after `metadata.completeness`. It remains a normal `AnalyzerInterface` implementation and does not require special Engine behavior.
