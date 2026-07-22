---
id: EG-038
title: "Built-in Analyzers End-to-End Validation"
summary: "Closes WP-106 with executable validation of the five built-in analyzers, strict-policy behavior and generator coexistence."
status: Approved
version: 1.0.0
category: "Engineering Standard"
document_class: NormativeDocument
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder, analyzer, end-to-end, validation]
depends_on: [EG-032, EG-033, EG-034, EG-035, EG-036, EG-037]
related_adrs: []
work_package: WP-106
---

# EG-038 — Built-in Analyzers End-to-End Validation

## 1. Purpose

This increment closes WP-106 by validating the built-in analyzer catalog through the real CLI composition root and the production repository pipeline.

## 2. Governed analyzer catalog

The default application MUST expose the following analyzers in this order:

1. `metadata.completeness`
2. `reference.integrity`
3. `document.consistency`
4. `repository.policy`
5. `generated.artifacts`

The order is part of the deterministic CLI catalog contract.

## 3. Pipeline contract

The end-to-end validation MUST prove that:

- discovery and indexing provide the workspace required by all analyzers;
- the five analyzers can execute in one production build;
- warning-only findings do not stop generation under strict policy;
- analyzer errors stop generation under strict policy;
- diagnostics retain their analyzer extension identifier;
- built-in generators continue to produce the five governed artifacts when generation is allowed.

## 4. Generated artifact warnings

`generated.artifacts` runs before the generator stage. Missing governed artifacts are therefore warnings. A strict build with only these warnings MUST continue and regenerate the artifacts in the same execution.

## 5. Reference failure scenario

A broken reference MUST produce `REFINT-201`. Under strict policy, the build MUST finish with a generation failure exit code and MUST NOT write generated artifacts.

## 6. Acceptance tests

The increment adds `BuiltInAnalyzersEndToEndTest` with three scenarios:

- catalog exposure and deterministic order;
- successful strict build with warning-only findings;
- strict build with a reference error and suppressed generation.

## 7. WP-106 closure

WP-106 is complete when this increment and the complete repository suite pass Composer validation, PHPUnit, PHPStan, CLI catalog inspection and Git whitespace validation.
