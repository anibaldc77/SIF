---
id: EG-035
title: Document Consistency Analyzer
summary: Defines the built-in analyzer that reports internally inconsistent document metadata.
document_class: GovernanceDocument
category: Work Package
status: Approved
version: 1.0.0
work_package: WP-106
authors:
  - SIF Engineering
tags:
  - builder
  - analyzer
  - metadata
created: 2026-07-22
updated: 2026-07-22
depends_on:
  - EG-032
related_adrs: []
references:
  - EG-032
  - EG-003
---

# EG-035 — Document Consistency Analyzer

## 1. Purpose

`document.consistency` validates internal relationships among fields of each registered metadata document. It is read-only, deterministic and independent of artifact generation.

## 2. Preconditions

The analyzer requires:

- a `RepositoryWorkspace`;
- a `MetadataRegistry`.

Missing inputs produce `ANALYZER-103` with error severity.

## 3. Functional diagnostics

| Code | Severity | Condition |
|---|---|---|
| `DOCCONS-201` | Error | The status is not registered by metadata governance. |
| `DOCCONS-202` | Error | The version does not conform to Semantic Versioning syntax. |
| `DOCCONS-203` | Error | The declared document class is incompatible with the category. |
| `DOCCONS-204` | Error/Warning | Lifecycle fields contradict status, including missing or premature `superseded_by`. |
| `DOCCONS-205` | Error | Dates are invalid or `updated` precedes `created`. |
| `DOCCONS-206` | Warning | The filename basename does not equal or begin with the document identifier. |

## 4. Filename convention

A filename is consistent when its basename is either the exact identifier or begins with the identifier followed by a hyphen. This permits descriptive filenames such as `EG-035-Document-Consistency-Analyzer.md`.

## 5. Defense in depth

Some rules overlap scanner validation intentionally. The analyzer protects programmatically assembled workspaces and future ingestion paths that may bypass the default scanner. Date chronology and filename alignment add checks not currently enforced by `CoreMetadataValidator`.

## 6. Determinism

Findings are sorted by code, document identifier, source path and message. Repeated inspection of equivalent registries produces structurally equal ordered results.

## 7. Integration

The default CLI composition registers the analyzer after `reference.integrity`. It remains a normal `AnalyzerInterface` extension and requires no Engine-specific branching.
