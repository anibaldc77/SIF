---
id: EG-298
title: Immutable Installation Value Model
summary: Defines the immutable identifiers, modes, options, request aggregate, dependency declarations, mutation classifications, rollback policies and typed validation failures used by the SIF Installer foundation.
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
  - foundation
  - installer
  - provisioning
  - value-objects
  - validation
  - security
depends_on:
  - EG-297
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-298 — Immutable Installation Value Model

## 1. Purpose

WP-216-I2 establishes the immutable vocabulary consumed by later requirement, planning, mutation, execution and runtime increments.

This increment introduces no probe, registry, dependency graph, filesystem access, configuration persistence, execution, verification, rollback execution or runtime integration.

## 2. Public model

The increment SHALL provide:

- `InstallationIdentifier`;
- `InstallationMode`;
- `InstallationOption`;
- `InstallationRequest`;
- `RequirementIdentifier`;
- `InstallationStepIdentifier`;
- `StepDependency`;
- `MutationClassification`;
- `RollbackPolicy`;
- typed validation exceptions rooted at `InstallerException`.

All value objects SHALL be immutable.

## 3. Identifiers

Installation, requirement and step identifiers SHALL:

- be non-empty after trimming;
- be at most 128 bytes;
- begin with an ASCII alphanumeric character;
- contain only ASCII alphanumeric characters, period, underscore, colon or hyphen;
- remain case-sensitive;
- expose stable string representations.

No identifier SHALL imply filesystem resolution or executable content.

## 4. Installation modes

Installation modes SHALL use lowercase portable tokens.

The governed vocabulary includes:

- `fresh`;
- `repair`;
- `upgrade`.

Safe extension tokens are permitted to preserve extensibility. Tokens SHALL begin with a lowercase letter and contain only lowercase letters, digits and hyphens.

## 5. Installation options

An installation option SHALL contain:

- a normalized lowercase name;
- a scalar-or-null value;
- an explicit sensitive flag.

Option names SHALL use portable dot notation and SHALL NOT contain path separators.

String values SHALL be bounded. Floating-point values SHALL be finite.

Sensitive options SHALL retain their actual value only through the explicit `value()` accessor. Diagnostic summaries SHALL replace sensitive values with `[REDACTED]`.

## 6. Installation request

An installation request SHALL aggregate:

- one installation identifier;
- one installation mode;
- an ordered list of unique installation options.

Option uniqueness SHALL be evaluated after name normalization.

The request summary SHALL preserve option order and SHALL always use each option's secret-safe diagnostic representation.

The request SHALL NOT authorize execution.

## 7. Step dependencies

A step dependency SHALL identify another installation step and declare whether the dependency is required.

Self-dependency validation SHALL be available when the owner step is known. Full graph validation, missing-dependency detection and cycle detection belong to WP-216-I4.

## 8. Mutation classifications

The governed vocabulary includes:

- `none`;
- `filesystem`;
- `configuration`;
- `secret-reference`;
- `infrastructure`;
- `migration`.

Safe extension tokens are allowed. A classification communicates declared intent only; it performs no mutation.

## 9. Rollback policies

The governed vocabulary includes:

- `unsupported`;
- `compensating`;
- `required`.

Safe extension tokens are allowed.

A rollback policy describes declared capability. It SHALL NOT claim transactional behavior and SHALL NOT execute compensation.

## 10. Failure model

Every invalid construction path SHALL throw a typed exception rooted at `InstallerException`.

Validation messages SHALL identify the invalid field without disclosing sensitive option values.

## 11. Security invariants

- No value object accesses the filesystem, environment, network or database.
- No constructor executes installation behavior.
- Sensitive option summaries are redacted.
- No metadata contains executable PHP, shell commands or evaluated expressions.
- Extension vocabularies remain bounded to safe portable tokens.
- Requests do not imply authorization.

## 12. Compatibility

The implementation is additive under `Sif\Foundation\Installer`.

No existing Foundation public API or runtime lifecycle signature is modified.

## 13. Acceptance criteria

I2 is accepted when:

1. all public values are immutable;
2. invalid identifiers and tokens fail with typed exceptions;
3. sensitive option summaries are redacted;
4. duplicate options fail after normalization;
5. request summaries are deterministic and preserve order;
6. self-dependency can be rejected when the owner is known;
7. no stateful infrastructure access is introduced;
8. focused PHPUnit tests succeed;
9. PHPStan level 8 succeeds;
10. governed metadata validates with zero diagnostics.
