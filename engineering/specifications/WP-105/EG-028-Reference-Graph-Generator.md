# EG-028 — Reference Graph Generator

- **Work Package:** WP-105
- **Increment:** 3
- **Status:** Implemented
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** EG-025, WP-101, WP-102, WP-103, WP-104
- **Generator identifier:** `reference.graph`

## 1. Purpose

`reference.graph` serializes the resolved repository reference topology into a deterministic, versioned JSON artifact suitable for automation and future visualization adapters.

## 2. Artifact

```text
build/reference-graph.generated.json
```

The artifact is owned exclusively by `reference.graph` and uses type `json`.

## 3. Schema

Top-level provenance is mandatory:

```json
{
  "schema_version": "1.0.0",
  "generated_by": "sif-builder",
  "generator": "reference.graph"
}
```

The payload contains:

- `summary`: node, edge, broken-reference, and cycle counts;
- `nodes`: every document in `RepositoryIndex`, including isolated documents;
- `edges`: resolved directed references;
- `broken_references`: unresolved references retained outside the executable graph;
- `cycles`: canonical closed identifier paths detected by WP-102.

## 4. Determinism

Nodes are naturally ordered by identifier. Edges and broken references are ordered by source, target, type, line, and reason where applicable. Cycles inherit the canonical deterministic order from `ReferenceCycleDetector`.

The renderer:

- uses UTF-8 JSON without escaped slashes or Unicode;
- uses stable insertion order for object keys;
- emits four-space pretty printing;
- terminates with exactly one `\n`;
- excludes timestamps, run identifiers, usernames, and absolute paths.

## 5. Missing inputs

When workspace, repository index, or resolution is unavailable, generation fails through diagnostic:

```text
GENERATOR-103
```

The condition is reported as an expected configuration failure and does not require an exception.

## 6. Architectural constraints

The generator:

- implements `GeneratorInterface`;
- returns a `GenerationResult`;
- never writes directly to disk;
- does not depend on CLI classes;
- uses `ReferenceGraph` and `ReferenceCycleDetector` from WP-102;
- leaves artifact collision and atomic persistence to WP-103;
- does not emit Mermaid or Graphviz in this increment.

## 7. CLI composition

The default composition root registers:

```text
repository.index
reference.report
reference.graph
```

Selection remains governed by the existing `--generator` option.

## 8. Tests

Required coverage:

1. graph view includes isolated index nodes;
2. resolved edges and broken references are separated;
3. cycles are canonical and deterministic;
4. JSON provenance and schema version are present;
5. generated artifact ownership, path, and type are correct;
6. missing resolution produces `GENERATOR-103`;
7. `list` exposes `reference.graph`.
