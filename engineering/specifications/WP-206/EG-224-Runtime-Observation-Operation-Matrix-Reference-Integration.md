---
id: EG-224
title: Runtime Observation Operation Matrix Reference Integration
summary: Defines a consolidated opt-in reference matrix for boot, run, and shutdown observation using valid independent runtime graphs.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-206
tags:
  - runtime
  - events
  - observation
  - lifecycle
  - integration
depends_on:
  - EG-223
  - EG-222
  - EG-221
  - EG-220
  - EG-219
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-224 — Runtime Observation Operation Matrix Reference Integration

## 1. Purpose

This increment consolidates the approved boot, run, and shutdown observation scenarios into a single reference matrix without introducing invalid lifecycle sequences or product runtime changes.

## 2. Matrix model

The matrix SHALL use an independent application graph for standalone `boot()` and a second independent application graph for the valid `run()` followed by `shutdown()` sequence.

The reference MUST NOT invoke `run()` after a standalone `boot()` on the same application unless the Runtime contract explicitly supports that sequence.

## 3. Boot matrix entry

The boot entry SHALL preserve the delegated `BootResult`, observe exactly `boot`, leave the Runtime in state `booted`, and produce no diagnostics.

## 4. Run and shutdown matrix entry

The run-shutdown entry SHALL preserve both delegated results, observe `run` followed by `shutdown`, leave the Runtime in state `stopped`, and produce no diagnostics.

## 5. Explicit composition

Each matrix entry SHALL compose `ListenerProvider`, `EventDispatcher`, isolated observation, and `ObservationLifecycleFacade` manually. The application Kernel MUST remain unchanged.

## 6. Executable reference

The repository SHALL include one executable example that prints the results, states, observed operation order, and diagnostic count for the matrix.

## 7. Compatibility boundary

This increment MUST NOT modify product runtime code, default application construction, lifecycle transitions, automatic capabilities, or event wiring.

## 8. Acceptance criteria

Acceptance requires focused and complete PHPUnit success, PHPStan level 8 with zero errors, successful example execution, Builder validation with zero diagnostics, and deterministic governed artifact generation.
