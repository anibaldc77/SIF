---
id: EG-348
title: Bootstrap Configuration and Environment Templates
summary: Defines deterministic application templates for bootstrap entry points, configuration, environment examples, Composer metadata, test configuration and cross-platform launchers.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - foundation
  - application-skeleton
  - templates
  - bootstrap
  - configuration
  - environment
depends_on:
  - EG-347
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-348 — Bootstrap, Configuration and Environment Templates

## 1. Purpose

WP-222 I4 defines the deterministic template layer used to transform a validated `ProjectManifest` into a `SkeletonBlueprint` containing the baseline bootstrap, configuration, environment, Composer, PHPUnit and launcher files of a SIF application.

## 2. Template rendering

Templates SHALL use explicit `{{name}}` placeholders. Rendering SHALL reject missing variables, unknown variables, unresolved placeholders and control characters. Template sources and rendered output SHALL use LF line endings.

Template rendering SHALL be independent from the filesystem and SHALL NOT read environment variables, secrets or process globals.

## 3. Generated files

The baseline factory SHALL generate:

- `bootstrap/app.php`;
- `bootstrap/cli.php`;
- `public/index.php`;
- `config/app.php`;
- `config/database.php`;
- `.env.example`;
- `.gitignore`;
- `composer.json`;
- `phpunit.xml`;
- `sif`;
- `sif.bat`;
- `sif.project.json`.

Every generated file SHALL be declared by the manifest and classified as `skeleton-owned`. Missing or incorrectly owned paths SHALL fail closed before a blueprint is returned.

## 4. Bootstrap separation

HTTP and CLI bootstraps SHALL remain separate. `public/index.php` SHALL load the HTTP bootstrap. `bootstrap/cli.php` SHALL compose the Developer CLI runtime without executing a command during inclusion.

## 5. Configuration and secrets

Generated configuration SHALL refer to environment placeholders and SHALL NOT embed credentials. `.env.example` SHALL contain only variable names and safe example defaults. The generator SHALL NOT create `.env`.

## 6. Composer and namespace mapping

`composer.json` SHALL derive its package identifier, PHP requirement, SIF constraint and PSR-4 namespace from the immutable project manifest. JSON output SHALL be deterministic, UTF-8 and LF terminated.

## 7. Cross-platform launchers

The skeleton SHALL include Unix and Windows launchers. Launchers SHALL remain thin and delegate to the installed SIF CLI entry point.

## 8. Ownership and idempotency

Template output SHALL be represented as I3 `SkeletonArtifact` values and inherit I3 planning, fingerprints, overwrite policy and idempotent execution. I4 SHALL NOT write files directly.

## 9. Deferred scope

I4 does not execute Composer, create secrets, run migrations, invoke Installer, register `app:create` or perform first-run authorization.
