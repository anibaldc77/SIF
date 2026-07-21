# EG-017 — Builder Reporting

- Work Package: WP-103
- Increment: 6
- Status: Proposed
- Version: 0.1.0
- Depends on: EG-011 through EG-016

## 1. Objective

Complete WP-103 with deterministic, presentation-only reporting over the immutable terminal `BuilderResult`.

## 2. Decisions

1. Reporters consume `BuilderResult` and never mutate engine state.
2. Reporters do not write files or access the repository.
3. JSON and Markdown are the initial governed formats.
4. Terminal results expose generated artifact descriptions and deterministic execution statistics.
5. Throwable objects remain private and are never serialized.
6. Existing constructor and factory calls remain compatible through optional trailing arguments.

## 3. Result enrichment

`BuilderResult` includes:

- terminal status;
- run identifier when available;
- completed phases;
- sorted diagnostics;
- sorted generated artifacts;
- deterministic statistics;
- safe failure summary.

Statistics contain counts only. Wall-clock timing is excluded because it would make snapshots non-reproducible.

## 4. Reporter contract

A reporter has a stable identifier, a media type, and renders one terminal result to a string.

Initial reporters:

- `report.json` — `application/json`;
- `report.markdown` — `text/markdown`.

## 5. Acceptance criteria

- JSON output is valid and stable.
- Markdown output contains summary, phases, diagnostics, artifacts, and failure information when present.
- Diagnostic and artifact ordering is inherited from governed collections.
- Reporting has no filesystem side effects.
- Existing engine execution remains source-compatible.
