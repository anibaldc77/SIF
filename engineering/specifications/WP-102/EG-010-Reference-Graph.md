---
id: EG-010
title: Reference Graph
summary: Defines the deterministic directed graph built from resolved repository references, including cycle detection and impact queries.
status: Draft
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-102
authors:
  - SIF Team
created: 2026-07-20
updated: 2026-07-20
tags:
  - builder
  - references
  - graph
depends_on:
  - EG-009
related_adrs: []
references:
  - EG-009
---

# EG-010 — Reference Graph

- Work Package: WP-102
- Increment: 4
- Status: Draft
- Version: 0.1.0

## Objective

Build a deterministic directed graph from resolved references and provide cycle detection and reverse-impact queries without coupling the graph to parsing, file I/O, diagram rendering, or repository mutation.

## Scope

- Build adjacency from `ResolutionResult::resolved`.
- Query outgoing and incoming resolved references.
- Enumerate graph nodes and edge count deterministically.
- Detect directed cycles, including self-references, without duplicate rotational representations.
- Calculate direct and transitive dependents for impact analysis.

## Exclusions

- Broken references are not graph edges.
- No Graphviz/Mermaid generation.
- No persistence or cache.
- No semantic policy deciding whether a reference type may form a dependency.
- No mutation of `RepositoryIndex` or `ResolutionResult`.

## Invariants

1. Every edge is a `ResolvedReference`.
2. Adjacency and public results are ordered lexicographically.
3. Cycle identities are canonical and deterministic.
4. Transitive impact never returns the queried identifier itself.
5. The graph is immutable after construction.
