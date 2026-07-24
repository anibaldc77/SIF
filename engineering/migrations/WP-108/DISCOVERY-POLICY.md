---
id: WP-108-DISCOVERY-POLICY
title: WP-108 Repository Discovery Policy
summary: Operational policy for separating governed SIF Markdown artifacts from dependency, generated, cache and temporary files.
status: Draft
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-22
updated: 2026-07-22
tags:
  - migration
  - discovery
  - exclusions
work_package: WP-108
depends_on:
  - EG-047
  - EG-048
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-108 Repository Discovery Policy

## Policy statement

SIF Builder validates SIF-owned Engineering Artifacts. It does not govern documentation installed by dependency managers, generated outputs, IDE metadata, caches or temporary workspaces.

## Default exclusions

The following directory segments are excluded before metadata parsing:

```text
.git
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

## Required inclusions

The policy does not exclude SIF-owned Markdown solely because it is:

- at repository root;
- below `engineering/`;
- below `src/`;
- below `tools/builder/docs/`;
- named `README.md`, `CHANGELOG.md`, `SECURITY.md` or `SUPPORT.md`.

Whether those documents require Front Matter is resolved by later WP-108 migration increments, not by hiding them during discovery.

## Non-negotiable rule

An exclusion must describe ownership or lifecycle boundaries. It must never be introduced solely to make a diagnostic disappear.

## Review checklist

Before adding a future exclusion, reviewers must verify:

- the path is not SIF-owned governance material;
- exclusion does not use substring matching;
- an inclusion regression test exists;
- an exclusion test exists;
- generated artifact verification remains active;
- the change is documented and versioned.
