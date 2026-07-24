---
id: EG-025
title: Built-in Generators Architecture
summary: Defines the architecture, catalog and governance principles for built-in SIF Builder generators.
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-105
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-21
tags:
  - builder
  - generators
  - architecture
depends_on: []
related_adrs: []
references: []
---

# EG-025 — Built-in Generators Architecture

- **Work Package:** WP-105
- **Status:** Proposed for implementation
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** WP-100, WP-101, WP-102, WP-103, WP-104
- **Target:** SIF Builder v2.0.0-alpha1

## 1. Purpose

WP-105 defines the architecture for the first production-ready generators distributed with SIF Builder. These generators transform the repository model, metadata, references, and execution context produced by WP-101 through WP-103 into deterministic, reviewable, and persistible artifacts.

Built-in generators are ordinary implementations of `GeneratorInterface`. They receive a `BuilderContext`, return a `GenerationResult`, and never write directly to the filesystem. Artifact collision detection, output-root enforcement, atomic publication, diagnostics, reporting, and CLI exit behavior remain responsibilities of the Engine and CLI layers already established.

## 2. Objectives

WP-105 shall:

1. establish a governed catalog of built-in generators;
2. define stable generator identifiers and artifact ownership;
3. generate useful repository documentation from existing Builder models;
4. preserve deterministic output across identical executions;
5. prevent generators from mutating repository state or writing files directly;
6. define overwrite, collision, and generated-file marker policies;
7. support selection through the existing CLI `--generator` option;
8. expose generator capabilities through the existing `list` command;
9. provide unit, integration, snapshot, and end-to-end validation;
10. preserve extension compatibility for third-party generators.

## 3. Non-goals

WP-105 does not introduce:

- automatic rewriting of source specifications;
- parsing of Git history or conventional commits;
- release publication to GitHub or package registries;
- PHP source-code generation;
- PHPDoc mutation of existing classes;
- test skeleton generation;
- AI-generated documentation;
- incremental caches;
- template engines or themes;
- Mermaid or Graphviz rendering in the first increment;
- plugin discovery outside the explicit composition root.

Those capabilities require later work packages or explicit architecture decisions.

## 4. Architectural position

```text
RepositoryScanner / Metadata / RepositoryIndex / References
                         |
                         v
                   BuilderContext
                         |
                         v
               Built-in Generator
                         |
                         v
                  GenerationResult
                         |
                         v
                  ArtifactCollection
                         |
                         v
                GeneratorStage (WP-103)
                         |
                         v
             AtomicArtifactWriter (WP-103)
```

Dependency direction:

```text
Built-in Generators -> Engine contracts and repository/reference models
Engine Core         -X-> Built-in Generators
CLI Commands        -X-> Concrete generator implementations
Composition Root    -> Registers Built-in Generators
```

## 5. Namespace and filesystem layout

Production code shall use:

```text
tools/builder/src/Generator/
    Contract/
    Exception/
    RepositoryIndex/
    ReferenceGraph/
    Documentation/
    Manifest/
    Support/
```

Tests shall use:

```text
tools/builder/tests/Generator/
```

Composer mappings:

```json
"Sif\\Builder\\Generator\\": "tools/builder/src/Generator/"
```

```json
"Sif\\Builder\\Tests\\Generator\\": "tools/builder/tests/Generator/"
```

Built-in generators shall not be placed under `Engine`, because the Engine must remain independent of concrete extensions.

## 6. Generator contract

Every built-in generator shall implement the public WP-103 contract:

```php
interface GeneratorInterface
{
    public function id(): string;

    public function generate(BuilderContext $context): GenerationResult;
}
```

A generator shall:

- expose a stable identifier;
- be stateless or behave as if stateless;
- derive output only from injected collaborators and `BuilderContext`;
- return zero or more `GeneratedArtifact` objects;
- return diagnostics instead of emitting console output;
- never call filesystem write operations;
- never call `exit()`;
- never mutate metadata, index, references, or workspace objects;
- never depend on CLI classes.

## 7. Built-in generator catalog

### 7.1 Initial governed identifiers

```text
repository.index
reference.report
reference.graph
repository.manifest
documentation.navigation
```

Identifiers are public API. Renaming or removing one requires a compatibility plan.

### 7.2 `repository.index`

Produces a deterministic Markdown repository index derived from `RepositoryIndex` and available reference information.

Default artifact:

```text
engineering/INDEX.generated.md
```

Minimum contents:

- generated-file notice;
- repository summary statistics;
- entries grouped by governed document type;
- identifier, title, status, version, and relative path;
- deterministic links;
- unresolved-reference summary when available;
- stable ordering.

This generator is the first implementation increment of WP-105.

### 7.3 `reference.report`

Produces human-readable and machine-readable reference reports.

Default artifacts:

```text
engineering/references.generated.md
build/references.generated.json
```

The JSON artifact is intended for automation and shall use a versioned schema.

### 7.4 `reference.graph`

Produces a serialized reference graph suitable for later visualization.

Initial default artifact:

```text
build/reference-graph.generated.json
```

Mermaid and Graphviz output are deferred until their formatting and escaping rules are governed.

### 7.5 `repository.manifest`

Produces a machine-readable repository manifest derived from the index, metadata, and reference resolution.

Default artifact:

```text
build/repository-manifest.generated.json
```

This artifact shall not replace existing handwritten `component.json` or `component.lock` files during WP-105.

### 7.6 `documentation.navigation`

Produces deterministic navigation data for future documentation publishing.

Default artifacts:

```text
docs/navigation.generated.json
docs/SUMMARY.generated.md
```

It shall not introduce a documentation site generator or theme.

## 8. Generator dependencies

Generators shall consume only data already available through `BuilderContext` and its `RepositoryWorkspace`.

Conceptual dependency matrix:

| Generator | Metadata registry | Repository index | References | Resolution | Graph |
|---|---:|---:|---:|---:|---:|
| `repository.index` | yes | yes | optional | optional | no |
| `reference.report` | no | yes | yes | yes | no |
| `reference.graph` | no | yes | yes | yes | yes |
| `repository.manifest` | yes | yes | yes | yes | optional |
| `documentation.navigation` | yes | yes | optional | optional | no |

If required workspace data is unavailable, the generator shall return a stable diagnostic rather than throw for normal configuration absence.

## 9. Artifact ownership

Each generated artifact shall have exactly one owning generator identifier.

Ownership rules:

1. two generators may not claim the same normalized relative path;
2. case-only path differences are collisions;
3. generators may not emit absolute paths;
4. generators may not escape `outputRoot`;
5. generators may not overwrite handwritten source documents by default;
6. generated paths must include a `.generated` marker unless explicitly governed otherwise;
7. artifact type and media type shall be stable and documented;
8. checksum calculation remains the responsibility of `GeneratedArtifact` and reporting.

Collision enforcement remains centralized in `ArtifactCollection` and `GeneratorStage`.

## 10. Generated-file marker

Human-readable generated artifacts shall begin with a clear marker.

Markdown example:

```text
<!-- Generated by SIF Builder. Do not edit manually. -->
```

JSON artifacts shall include top-level provenance fields:

```json
{
    "schema_version": "1.0.0",
    "generated_by": "sif-builder",
    "generator": "repository.manifest"
}
```

The marker shall not include timestamps, random identifiers, machine paths, usernames, or environment-dependent values unless explicitly requested by a future reproducibility policy.

## 11. Determinism and reproducibility

For identical inputs and generator configuration, artifact bytes shall be identical.

Generators shall therefore:

- use explicit stable sorting;
- normalize line endings to `\n`;
- terminate textual artifacts with exactly one newline;
- serialize JSON with stable key and list ordering;
- avoid timestamps by default;
- avoid absolute repository paths;
- avoid run identifiers in artifact content;
- escape Markdown and JSON deterministically;
- avoid locale-dependent formatting.

Snapshot tests may be used only when snapshots are small, reviewable, and committed as test fixtures.

## 12. Formatting support

Shared formatting behavior shall live under:

```text
Sif\Builder\Generator\Support
```

Potential support components include:

- `MarkdownEscaper`;
- `RelativeLinkBuilder`;
- `StableJsonEncoder`;
- `GeneratedFileHeader`;
- `DocumentTypeLabelProvider`.

Support components shall remain narrowly scoped. A general template engine is not authorized by WP-105.

## 13. Diagnostics

Built-in generator diagnostics shall use the `GENERATOR-*` namespace with generator-specific ranges.

Initial allocation:

```text
GENERATOR-101  Required workspace data unavailable
GENERATOR-102  Unsupported metadata or document type
GENERATOR-201  Invalid relative link target
GENERATOR-301  Serialization failure
GENERATOR-401  Artifact ownership or path policy violation
GENERATOR-500  Unexpected generator failure (Engine wrapper)
```

Diagnostics shall include safe context such as generator identifier and repository-relative document identifier. They shall not expose stack traces, secrets, or absolute filesystem paths by default.

## 14. Registration and default composition

The CLI composition root introduced by WP-104 shall register built-in generators explicitly and deterministically.

Expected order after all WP-105 increments:

```text
repository.index
reference.report
reference.graph
repository.manifest
documentation.navigation
```

The registration order is observable through `sif-builder list` and defines the default execution order when no `--generator` option is supplied.

No runtime reflection or directory scanning shall be used to discover generators.

## 15. Selection semantics

Existing CLI behavior remains authoritative:

```text
sif-builder build --generator=repository.index
sif-builder build --generator=repository.index --generator=reference.report
```

Rules:

- omitted selection executes all registered generators;
- explicit selection preserves requested order;
- unknown identifiers produce the existing configuration diagnostics;
- `validate` and `build --no-write` remain analysis-only and do not run built-in generators;
- generators shall not infer selection from output format.

## 16. Repository Index Generator design

### 16.1 Components

The first implementation increment shall introduce at minimum:

```text
RepositoryIndexGenerator
RepositoryIndexMarkdownRenderer
RepositoryIndexView
RepositoryIndexSection
RepositoryIndexEntryView
```

A renderer contract may be introduced if it materially improves testing and future formats, but unnecessary abstraction shall be avoided.

### 16.2 Input

The generator requires:

- `BuilderContext::workspace()`;
- `RepositoryWorkspace::repositoryIndex`;
- optional reference resolution statistics;
- repository-relative document paths.

### 16.3 Output

The generator returns one `GeneratedArtifact`:

```text
generator: repository.index
path: engineering/INDEX.generated.md
type: markdown
```

### 16.4 Ordering

Entries shall be ordered by:

1. governed document-type rank;
2. normalized identifier;
3. normalized relative path as final tie-breaker.

The initial type rank shall be documented in the increment specification and shall not depend on discovery order.

### 16.5 Link generation

Links in `engineering/INDEX.generated.md` shall be relative to the artifact location. Link creation must normalize separators to `/` and escape Markdown-sensitive characters.

## 17. JSON schema versioning

Machine-readable artifacts shall expose `schema_version` independently from the Builder application version.

Rules:

- additive compatible fields may retain the same major schema version;
- removing or changing field meaning requires a major schema version change;
- keys shall use `snake_case`;
- unknown fields must be safely ignorable by consumers;
- JSON output shall be UTF-8 and pretty-printed deterministically.

Formal JSON Schema documents may be introduced in the increment that first produces each machine-readable artifact.

## 18. Error handling

Expected input limitations shall be represented as diagnostics in `GenerationResult`.

Unexpected implementation failures may throw. `GeneratorStage` will convert them to `GENERATOR-500`, preserving the Engine lifecycle and safe failure behavior.

A generator shall not catch `Throwable` merely to hide programming errors. Catching shall be limited to errors that can be converted into meaningful domain diagnostics.

## 19. Testing strategy

Each generator increment shall provide:

1. unit tests for formatting and escaping;
2. model-to-view transformation tests;
3. deterministic ordering tests;
4. generated path policy tests;
5. exact artifact-content tests;
6. integration tests through `GeneratorStage`;
7. CLI registration tests;
8. full-suite PHPUnit validation;
9. PHPStan level 8 validation;
10. `git diff --check` validation.

Tests shall include Windows-style source paths to protect cross-platform behavior.

## 20. Security and safety

Generators shall treat repository metadata as untrusted text.

They must:

- escape Markdown table delimiters and control characters;
- encode JSON using a strict encoder;
- reject null bytes;
- avoid embedding absolute paths;
- prevent path traversal through artifact paths;
- avoid evaluating templates or source content;
- avoid loading arbitrary PHP files;
- avoid network access;
- avoid following external links during generation.

## 21. Performance

The initial implementation shall favor clarity and determinism. Nevertheless:

- generators should iterate repository collections linearly where practical;
- repeated index scans should be avoided through small immutable views;
- graph generation shall reuse WP-102 graph components;
- complete repository contents shall not be loaded when metadata and index data are sufficient;
- premature caching is prohibited without measurement.

## 22. Public API and compatibility

The following become public or governed behavior:

- built-in generator identifiers;
- default artifact relative paths;
- machine-readable schema versions;
- default registration order;
- diagnostic identifiers;
- generated-file markers;
- selection behavior exposed through the CLI.

Internal renderer and view classes may remain non-public implementation details unless exposed through documented contracts.

## 23. Implementation increments

### Increment 1 — Repository Index Generator

- `repository.index` implementation;
- deterministic Markdown rendering;
- generated-file marker;
- registration in default CLI composition;
- unit and integration tests;
- EG-026.

### Increment 2 — Reference Report Generator

- `reference.report` implementation;
- Markdown and versioned JSON artifacts;
- broken-reference reporting;
- EG-027.

### Increment 3 — Reference Graph Generator

- `reference.graph` implementation;
- versioned graph JSON;
- cycles and impact data when available;
- EG-028.

### Increment 4 — Repository Manifest Generator

- `repository.manifest` implementation;
- versioned manifest JSON;
- metadata, entries, references, and checksums where available;
- EG-029.

### Increment 5 — Documentation Navigation Generator

- `documentation.navigation` implementation;
- Markdown summary and JSON navigation;
- stable hierarchy and links;
- EG-030.

### Increment 6 — Built-in Generator Integration and E2E Validation

- final default registration catalog;
- `list` command visibility;
- multi-generator collision validation;
- build smoke tests;
- compatibility and documentation review;
- EG-031.

## 24. Acceptance criteria

WP-105 is complete when:

1. all five governed built-in generator identifiers are implemented;
2. all generators use `GeneratorInterface` and return `GenerationResult`;
3. no generator writes directly to the filesystem;
4. artifact paths and schemas are documented;
5. output is deterministic across repeated executions;
6. generated artifacts contain provenance markers;
7. default CLI composition registers generators in governed order;
8. explicit CLI generator selection works;
9. analysis-only commands do not run generators;
10. collision, escaping, invalid workspace, and serialization scenarios are tested;
11. PHPUnit passes for generator, CLI, Engine, and full suites;
12. PHPStan level 8 passes with zero errors;
13. Composer validation and optimized autoload generation pass;
14. end-to-end build execution produces the expected artifacts and exit code.

## 25. Architectural decisions

WP-105 establishes the following decisions:

- Built-in generators are extensions, not Engine branches.
- Generation and persistence remain separate concerns.
- Generated files use explicit `.generated` paths by default.
- Determinism takes precedence over timestamps and environment provenance.
- Concrete generators are registered only in the composition root.
- Machine-readable artifacts use independent versioned schemas.
- Repository Index Generator is the first vertical implementation.

## 26. Next action

After approval and integration of EG-025, implementation shall begin with:

```text
WP-105 — Increment 1
EG-026 — Repository Index Generator
```
