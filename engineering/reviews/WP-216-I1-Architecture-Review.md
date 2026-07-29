---
id: WP-216-I1-ARCHITECTURE-REVIEW
title: WP-216 I1 Architecture Review
summary: Reviews the proposed Installer and Application Provisioning Foundation boundaries, security invariants, lifecycle and implementation sequence.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-29
updated: 2026-07-29
tags:
  - installer
  - provisioning
  - architecture
  - security
  - review
work_package: WP-216
depends_on:
  - EG-297
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-216 I1 Architecture Review

## Scope reviewed

- Installer and provisioning subsystem boundaries;
- immutable request and plan model;
- requirement evaluation;
- deterministic step planning;
- safe mutation architecture;
- dry-run behavior;
- execution and verification;
- compensating rollback;
- module and infrastructure extension points;
- optional runtime integration;
- eight-increment delivery roadmap.

## Architectural decision

WP-216 SHALL be implemented as a planning-first subsystem.

Planning and execution are separate responsibilities. Installation never runs as a side effect of application construction or boot. Infrastructure access is available only through injected contracts.

## Security review

The architecture establishes the following mandatory controls:

- explicit execution authorization;
- filesystem confinement to authorized roots;
- rejection of silent overwrites;
- time-of-check revalidation;
- secret redaction;
- prohibition of executable installation metadata;
- typed failures;
- explicit rollback capabilities;
- immutable plans;
- no mutation during dry-run.

These controls are normative for all later increments.

## Compatibility review

The Work Package is additive. Existing applications remain valid without configuring Installer services. Runtime lifecycle signatures and existing Configuration, Module, Logging, Error Handling and Resource behavior remain unchanged.

## Dependency review

WP-216 may consume stable contracts from:

- Configuration 2.0;
- Modules 2.0;
- Structured Logging 2.0;
- Error Handling and Recovery 2.0;
- Resource Management and Asset Foundation.

It SHALL NOT introduce reverse dependencies from those subsystems to Installer.

Database migrations remain independently governed.

## Rollback review

Rollback is intentionally described as compensation rather than a universal transaction. Each mutation must declare whether rollback is available. The original execution failure remains the primary cause even when rollback also fails.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---:|---|
| Installer becomes a deployment platform | High | Keep remote deployment, package download and OS administration out of scope. |
| Planning performs hidden mutations | High | Enforce read-only planner and probe contracts. |
| Secret leakage | Critical | Redacted diagnostic representations and exclusion from fingerprints. |
| Filesystem escape | Critical | Reuse authorized-root and safe-relative-path concepts. |
| Implicit overwrites | High | Require explicit overwrite policy and immediate precondition validation. |
| Non-deterministic module contributions | High | Stable identities, duplicate detection, priority and registration order. |
| Rollback claims exceed adapter capability | High | Per-step rollback declaration and partial-rollback status. |
| Migration subsystem becomes coupled | Medium | Depend only on a future narrow adapter contract. |

## Increment decision

I2 may begin after approval of EG-297.

I2 SHALL implement only the immutable installation value model and typed exceptions. It SHALL NOT implement probes, registries, filesystem access, execution, rollback, runtime integration or external adapters.

## Review outcome

The architecture is suitable to begin incremental implementation subject to repository validation of the governed metadata.
