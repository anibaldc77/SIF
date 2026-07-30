---
id: EG-303
title: Dry-Run, Explicit Authorization and Installer Orchestration
summary: Defines deterministic dry-run reports and explicit execution authorization bound to an installation request and immutable mutation plan.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-216
tags:
  - installer
  - dry-run
  - authorization
  - orchestration
depends_on:
  - EG-302
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-303 — Dry-Run, Explicit Authorization and Installer Orchestration

## 1. Purpose

This increment joins requirement assessment, immutable planning, dry-run review and authorized execution without adding infrastructure side effects to the orchestration layer.

## 2. Dry-run

A dry-run SHALL expose the normalized request, requirement results, ordered steps, mutation declarations, warnings, executability and plan fingerprint. Sensitive installation options SHALL remain redacted. Equivalent inputs SHALL produce equivalent summaries.

## 3. Authorization

Execution authorization SHALL be explicit and immutable. It SHALL identify the installation, bind to the exact mutation-plan SHA-256 fingerprint and state whether mutation is permitted. An authorization for another installation, another plan or review-only use SHALL be rejected before any handler is invoked.

## 4. Orchestration

The orchestrator SHALL reject execution when required requirements fail. It SHALL NOT re-plan, infer mutations or mutate during dry-run. Once validation succeeds it SHALL delegate only the already compiled `MutationPlan` to `MutationPlanExecutor`.

## 5. Security invariants

- dry-run performs no mutation;
- secrets remain redacted in summaries;
- authorization is plan-specific and installation-specific;
- failed requirements prevent execution;
- authorization mismatch is a typed failure;
- application boot never authorizes installation implicitly.

## 6. Non-goals

This increment does not add web, CLI, persistence, token signing, user identity, clocks, filesystem handlers or runtime boot integration.
