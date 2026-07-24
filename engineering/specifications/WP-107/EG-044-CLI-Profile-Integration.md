---
id: EG-044
title: CLI Profile Integration
summary: Integrates repository configuration and build profiles into build and validate without adding profile awareness to the Builder Engine.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - profile
  - integration
work_package: WP-107
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-044 — CLI Profile Integration

## Status
Implemented

## Scope
Integrates repository configuration and build profiles into `build` and `validate` without adding profile awareness to the Builder Engine.

## CLI options
- `--profile=<identifier>` selects a declared profile.
- `--configuration=<path>` overrides `.sif/builder.json`.
- Explicit `--analyzer`, `--generator`, `--format`, `--strict`, `--lenient`, or `--policy` values override profile defaults.

## Runtime flow
1. Resolve repository and configuration paths.
2. Load and validate repository configuration.
3. Resolve inheritance and selected profile.
4. Validate extension identifiers.
5. Configure repository policies through registered factories.
6. Map the resolved profile to a regular `BuilderRequest`.
7. Execute the unchanged Builder Engine.
8. Restrict reporting to reporters enabled by the profile.

## Compatibility
When `.sif/builder.json` is absent, the built-in default configuration reproduces the previous five analyzers, five generators, two reporters, and lenient profile policy. Explicit CLI strictness remains authoritative.

## Security
The integration does not instantiate arbitrary class names, execute configuration code, interpolate environment variables, or access remote configuration.
