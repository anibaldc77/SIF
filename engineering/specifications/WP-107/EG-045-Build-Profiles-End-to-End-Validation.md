---
id: EG-045
title: "Build Profiles End-to-End Validation"
summary: "Closes WP-107 with executable validation of repository configuration, profile inheritance, extension selection, repository policies and CLI overrides."
status: Approved
version: 1.0.0
category: "Engineering Standard"
document_class: NormativeDocument
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder, configuration, profiles, cli, end-to-end]
depends_on: [EG-039, EG-040, EG-041, EG-042, EG-043, EG-044]
related_adrs: []
work_package: WP-107
---

# EG-045 — Build Profiles End-to-End Validation

## 1. Purpose

This increment closes WP-107 by validating repository configuration and build profiles through the real CLI composition root and production Builder pipeline.

## 2. Compatibility contract

When `.sif/builder.json` is absent, the CLI MUST preserve the built-in catalog and the pre-WP-107 behavior:

- five built-in analyzers;
- five built-in generators;
- Markdown and JSON reporters;
- lenient execution by default.

A default build MUST remain capable of producing all five governed artifacts.

## 3. Profile resolution contract

The end-to-end validation MUST prove that:

- the configured default profile is selected when `--profile` is absent;
- an explicit `--profile` selects the requested profile;
- inherited analyzers, generators and reporters are resolved deterministically;
- explicit empty extension lists disable inherited extensions;
- execution strictness is inherited and may be replaced by a child profile.

## 4. CLI precedence contract

Explicit CLI selections MUST take precedence over profile values for:

- analyzers;
- generators;
- reporter format;
- strict or lenient execution policy.

Profile selection and configuration-file selection are configuration concerns and MUST NOT leak into the engine request as unsupported options.

## 5. Repository policy contract

Repository policies declared in configuration MUST be constructed only through registered policy factories. The `repository.policy` analyzer MUST evaluate the policy set resolved for the current CLI execution.

A configured error-level policy finding under strict execution MUST suppress generation and preserve the policy diagnostic and rule identifier.

## 6. Configuration failure contract

Unknown extension identifiers MUST fail before engine execution. The CLI MUST return invalid usage and expose the corresponding deterministic diagnostic:

- `CONFIG-109` for analyzers;
- `CONFIG-110` for generators;
- `CONFIG-111` for reporters.

No partial build may be performed after configuration failure.

## 7. Acceptance tests

The increment adds `BuildProfilesEndToEndTest` with the following scenarios:

1. missing configuration preserves the built-in default build;
2. inherited profile and configured repository policy are applied through the production CLI;
3. explicit generator and policy CLI options replace profile values;
4. an unknown configured generator fails with `CONFIG-110` before engine execution.

## 8. WP-107 closure

WP-107 is complete when:

- increments 1–6 are integrated;
- the focused configuration and end-to-end tests pass;
- the complete PHPUnit suite passes;
- PHPStan level 8 reports no errors;
- Composer validation succeeds;
- CLI catalog inspection succeeds;
- Git whitespace validation succeeds.
