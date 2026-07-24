---
id: EG-048
title: Repository Discovery Normalization
summary: Defines deterministic discovery boundaries for governed Markdown artifacts and excludes external, generated, cached and transient trees before parsing.
status: Draft
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
# document_class intentionally omitted until WP-108 metadata migration establishes the canonical class for EG artifacts.
authors:
  - SIF Architecture Board
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - discovery
  - repository
  - migration
work_package: WP-108
depends_on:
  - EG-047
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-048 — Repository Discovery Normalization

## 1. Purpose

This increment defines the repository discovery boundary used by SIF Builder before metadata parsing, indexing, analysis or generation.

The objective is to remove false-positive diagnostics caused by third-party dependencies, generated output, caches and transient working directories without weakening validation of SIF-owned engineering artifacts.

## 2. Problem Statement

The WP-108 baseline completed all seven Builder phases and reported 386 diagnostics. A material portion originated from Markdown files under dependency trees such as `vendor/` and `tools/builder/vendor/`.

Those files are not SIF Engineering Artifacts and SHALL NOT enter the governed document pipeline.

Discovery normalization is therefore a boundary correction, not a diagnostic suppression mechanism.

## 3. Scope

This increment covers:

- deterministic path normalization;
- default excluded directory segments;
- pruning before file parsing;
- Markdown-only candidate selection;
- preservation of SIF-owned documentation;
- Windows and POSIX separator compatibility;
- unit, integration and end-to-end verification requirements.

## 4. Out of Scope

This increment does not:

- add or repair YAML Front Matter;
- infer document metadata;
- change analyzer severity;
- remove diagnostics for governed files;
- correct broken references;
- generate missing governed artifacts;
- introduce `.gitignore` semantics;
- read arbitrary ignore files.

## 5. Governed Discovery Boundary

A file is a discovery candidate only when all of the following are true:

1. It is located below the selected repository root.
2. Its extension is `.md`, compared case-insensitively.
3. No normalized path segment belongs to the default excluded segment set.
4. It is not an output artifact already represented by the Builder artifact model.
5. It is a regular readable file.

Passing discovery does not imply metadata validity. Parsing and analyzers remain responsible for validating governed documents.

## 6. Default Excluded Segments

The default policy SHALL exclude complete directory segments named:

```text
.git
.github/workflows cache directories only when represented as a dedicated segment
.idea
.vscode
node_modules
vendor
build
dist
coverage
.cache
.phpunit.cache
.phpstan.cache
.generated
generated
tmp
temp
```

### 6.1 Segment semantics

Exclusions SHALL be matched as path segments, not substrings.

Examples:

- `vendor/package/README.md` is excluded.
- `tools/builder/vendor/phpunit/README.md` is excluded.
- `engineering/vendor-policy.md` is not excluded merely because its filename contains `vendor`.
- `engineering/build-profiles.md` is not excluded merely because its filename contains `build`.

### 6.2 Repository-owned generated artifacts

The following governed output paths SHALL be excluded from source discovery even when the output directory is customized:

```text
engineering/INDEX.generated.md
engineering/REFERENCES.generated.md
engineering/NAVIGATION.generated.md
build/reference-graph.generated.json
build/repository-manifest.generated.json
```

JSON outputs are already outside Markdown discovery. Markdown outputs SHALL still be explicitly protected against re-indexing.

## 7. Path Normalization

Before matching, paths SHALL:

1. be made relative to the repository root;
2. replace `\\` with `/`;
3. collapse repeated separators;
4. remove a leading `./`;
5. preserve filename case for reporting;
6. compare excluded directory segments case-insensitively on every supported platform.

The implementation SHALL NOT require `realpath()` to succeed for every candidate because fixtures and virtual filesystems may use non-canonical paths.

## 8. Traversal Requirements

The discovery implementation SHOULD prune excluded directories during traversal instead of collecting every file and filtering afterward.

If the existing repository abstraction cannot prune traversal without a public API break, filtering after enumeration MAY be used temporarily, provided:

- excluded files are never opened or parsed;
- ordering remains deterministic;
- the implementation is covered by tests;
- the limitation is recorded in the implementation report.

## 9. Deterministic Ordering

Candidate paths SHALL be sorted by normalized repository-relative path using bytewise ascending order before parsing.

The same repository contents SHALL produce the same candidate order on Windows and POSIX systems.

## 10. Configuration Boundary

WP-108 Increment 2 introduces only the built-in exclusion policy.

User-configurable include/exclude patterns are deferred. They require a separate schema and precedence design and SHALL NOT be introduced implicitly in this migration increment.

## 11. Required Test Contract

The production discovery component SHALL be verified with tests covering:

1. Markdown files under `engineering/` are discovered.
2. Root-owned Markdown files remain discoverable unless explicitly classified as non-governed by a later policy.
3. Root `vendor/` is excluded.
4. Nested `tools/builder/vendor/` is excluded.
5. `node_modules/`, `.git/`, `build/`, `coverage/`, `tmp/` and `temp/` are excluded.
6. Filenames containing excluded words are not excluded.
7. Both `/` and `\\` separators are normalized.
8. Segment matching is case-insensitive.
9. Generated Markdown artifact paths are excluded.
10. Candidate ordering is deterministic.
11. Excluded invalid Markdown files produce no `REPOSITORY-101` diagnostics.
12. Invalid governed Markdown files still produce `REPOSITORY-101` diagnostics.

## 12. End-to-End Acceptance Scenario

A fixture repository SHALL contain:

```text
engineering/specifications/WP-108/EG-048.md
engineering/invalid.md
vendor/package/README.md
tools/builder/vendor/package/CHANGELOG.md
node_modules/package/README.md
engineering/build-profiles.md
engineering/INDEX.generated.md
```

Expected behavior:

- `EG-048.md`, `invalid.md` and `build-profiles.md` enter discovery;
- external dependency files do not enter discovery;
- `INDEX.generated.md` does not enter source discovery;
- the invalid governed file continues to produce its normal metadata diagnostic;
- excluded invalid files produce no diagnostics.

## 13. Compatibility

This is a backward-compatible behavioral correction for repository traversal.

No analyzer, generator, reporter, CLI argument, configuration schema or public result type may change.

## 14. Risks

### 14.1 Over-exclusion

A broad substring or absolute-path rule could hide legitimate SIF documentation.

Mitigation: segment-based matching and positive tests for filenames containing excluded words.

### 14.2 Platform divergence

Windows separators and case behavior could differ from CI environments.

Mitigation: normalize separators and compare policy segments case-insensitively in platform-independent code.

### 14.3 Hidden regression in generated artifact analysis

Excluding generated files from source discovery must not disable `generated.artifacts`, which verifies expected output through its own artifact contract.

Mitigation: test the analyzer independently and do not alter its registry or logic.

## 15. Acceptance Criteria

Increment 2 is accepted when:

- discovery excludes all default external and transient directory segments;
- excluded files are not parsed;
- governed files continue to be validated;
- generated Markdown outputs are not rediscovered as sources;
- path ordering is deterministic across separators;
- all new tests pass;
- the full PHPUnit suite passes;
- PHPStan level 8 passes;
- Composer validation passes;
- no analyzer severity or metadata rule is weakened;
- a new `php bin/sif-builder validate` run shows a reduction attributable only to excluded non-governed files.

## 16. Expected Baseline Effect

The diagnostic total is expected to decrease substantially because dependency Markdown trees under root `vendor/` and `tools/builder/vendor/` no longer participate in discovery.

No exact target count is normative until the production patch is executed against the current repository state.

## Revision History

| Version | Date | Status | Description |
|---|---|---|---|
| 0.1.0 | 2026-07-22 | Draft | Defines deterministic repository discovery boundaries and exclusion test contract. |
