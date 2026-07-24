---
id: REPOSITORY-DISCOVERY
title: Repository discovery
summary: SIF Builder discovers Markdown source candidates below the selected repository root before parsing metadata.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - repository
  - discovery
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Repository discovery

SIF Builder discovers Markdown source candidates below the selected repository root before parsing metadata.

## Built-in exclusions

Dependency, generated, cache and temporary directory segments are excluded by the built-in discovery policy:

```text
.git, .idea, .vscode, node_modules, vendor, build, dist,
coverage, .cache, .phpunit.cache, .phpstan.cache,
.generated, generated, tmp, temp
```

Matching applies to complete path segments and is case-insensitive. A file such as `engineering/vendor-policy.md` remains eligible because `vendor` is not a directory segment in that path.

## Generated documents

Builder-generated Markdown indexes and navigation files are outputs, not source documents. They are not rediscovered during subsequent runs.

The `generated.artifacts` analyzer remains responsible for reporting whether required outputs are missing or stale.

## Validation behavior

Exclusion occurs before metadata parsing. Therefore third-party Markdown without YAML Front Matter produces no repository diagnostics.

SIF-owned Markdown remains subject to the complete metadata, consistency, reference and policy analyzer set.

## Configuration

The initial WP-108 policy is built in and deterministic. Custom ignore patterns are not part of this increment.
