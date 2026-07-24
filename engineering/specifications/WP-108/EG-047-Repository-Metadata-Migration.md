---
id: EG-047
title: "Repository Metadata Migration"
summary: "Defines the governed migration of SIF repository documentation into the metadata model enforced by SIF Builder."
status: Draft
version: 1.0.0
category: Work Package
document_class: GovernanceDocument
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder, metadata, migration, repository]
depends_on: [EG-003, EG-032, EG-039, EG-046]
related_adrs: []
work_package: WP-108
---

# Repository Metadata Migration

## 1. Purpose

WP-108 shall migrate the SIF repository documentation to the metadata and consistency rules already enforced by SIF Builder. The work package shall not weaken analyzers, suppress valid diagnostics, or redefine the document model merely to reduce the diagnostic count.

## 2. Baseline

The accepted WP-107 validation completed successfully at the software quality level:

- 347 PHPUnit tests;
- 992 assertions;
- PHPStan level 8 with zero errors;
- configuration and end-to-end suites passing;
- Builder pipeline completing all seven phases.

The repository validation baseline contains 386 diagnostics and zero generated artifacts.

| Diagnostic | Count | Meaning |
|---|---:|---|
| REPOSITORY-101 | 345 | Source cannot be interpreted as governed metadata or contains invalid metadata values. |
| METACOMP-202 | 4 | Required metadata value is empty. |
| METACOMP-203 | 17 | Recommended metadata field is absent. |
| DOCCONS-203 | 4 | Category and document class are inconsistent. |
| DOCCONS-205 | 1 | Created or updated date is invalid. |
| DOCCONS-206 | 8 | Document identifier and filename are inconsistent. |
| REFERENCE-404 | 1 | Referenced document cannot be resolved. |
| REFINT-201 | 1 | Reference integrity analyzer reports the missing target. |
| GENART-201 | 5 | Governed generated artifacts are absent. |
| **Total** | **386** | Accepted migration baseline. |

## 3. Objectives

WP-108 shall:

1. define which repository paths are governed documentation;
2. exclude dependency, cache, build, and generated-output paths from discovery;
3. inventory every governed Markdown document;
4. assign or normalize document identity, category, class, status, version, authorship, dates, tags, dependencies, and related ADRs;
5. repair resolvable references without fabricating nonexistent documents;
6. preserve document body content unless a consistency correction requires an explicit amendment;
7. regenerate governed artifacts only after blocking diagnostics are removed;
8. produce a reproducible migration report.

## 4. Non-goals

WP-108 shall not:

- modify Builder diagnostics solely to accommodate legacy documents;
- add YAML Front Matter to third-party dependency documentation;
- migrate generated artifacts as source documents;
- silently invent historical authors, dates, approvals, dependencies, or ADR relationships;
- rewrite substantive engineering decisions;
- require a fully automated migration when source facts are unavailable.

## 5. Governed scope

The initial governed scope is:

- `/engineering/**`;
- `/tools/builder/docs/**`;
- selected first-party root documents expressly registered by repository policy;
- selected first-party component documentation under `/src/**` and `/tools/**`.

The following paths shall be excluded by default:

- `/.git/**`;
- `/.idea/**`;
- `/.vscode/**`;
- `/vendor/**`;
- `/tools/builder/vendor/**`;
- `/build/**`;
- `/coverage/**`;
- `/tmp/**`;
- generated files matching `*.generated.*`.

Root-level community documents such as `README.md`, `CHANGELOG.md`, `LICENSE`, `SECURITY.md`, `SUPPORT.md`, `CONTRIBUTING.md`, and `CODE_OF_CONDUCT.md` require an explicit governance decision. They shall not automatically receive engineering metadata merely because they are Markdown files.

## 6. Migration strategy

Migration shall proceed in deterministic waves.

### Wave A — Discovery boundaries

Introduce or configure repository discovery exclusions. Confirm that third-party dependency documents no longer produce diagnostics.

### Wave B — Canonical schema and classification

Create a classification matrix mapping repository paths and document purposes to registered categories and document classes.

### Wave C — Active engineering documents

Migrate current specifications, standards, models, governance documents, and active implementation reports.

### Wave D — Legacy engineering documents

Migrate or explicitly archive historical documents. Unknown historical facts shall be represented through an approved migration convention, not fabricated values.

### Wave E — Reference repair

Repair identifiers, filename relationships, dependencies, ADR references, and unresolved links.

### Wave F — Generated artifacts

Run all analyzers, then generate the five governed artifacts only when no blocking diagnostic remains.

## 7. Invariants

- Every migrated document must retain a stable identifier.
- A document identifier must be unique across the governed repository.
- Category and document class must be registered and compatible.
- Dates must use `YYYY-MM-DD`.
- Lists must remain lists after serialization, including empty lists.
- Migration tooling must be idempotent.
- A second run over an unchanged repository must produce no source changes.
- Manual exceptions must be documented and reviewable.
- Excluded paths must not disappear implicitly; exclusion rules must be versioned.

## 8. Safety model

Before modifying any document, the migration process shall capture:

- relative path;
- original SHA-256;
- proposed metadata;
- migration action;
- resulting SHA-256;
- diagnostic codes addressed;
- whether manual review is required.

Automated edits shall be limited to deterministic transformations supported by repository evidence. Ambiguous classification, authorship, lifecycle status, dates, and references shall be routed to manual review.

## 9. Deliverables

WP-108 is expected to deliver:

- governed discovery/exclusion configuration;
- repository document inventory;
- classification matrix;
- migration planner and dry-run output;
- deterministic metadata transformer where justified;
- migrated first-party documents;
- reference repair report;
- regenerated governed artifacts;
- implementation and completion report.

## 10. Acceptance criteria

WP-108 is complete when:

1. Composer validation passes;
2. the full PHPUnit suite passes;
3. PHPStan level 8 reports zero errors;
4. dependency and generated-output paths are excluded by explicit policy;
5. all governed first-party documents contain valid metadata;
6. no blocking metadata, consistency, or reference diagnostic remains;
7. Builder reports `succeeded` for the governed repository profile;
8. exactly five governed artifacts are generated;
9. a second validation and generation run is deterministic;
10. the migration report records every automatic and manual decision.

## 11. Increment plan

- Increment 1: migration foundation and baseline.
- Increment 2: discovery exclusions and governed scope.
- Increment 3: inventory and classification model.
- Increment 4: migration planner and dry-run reporting.
- Increment 5: active engineering document migration.
- Increment 6: legacy and root-document governance decisions.
- Increment 7: reference repair and consistency normalization.
- Increment 8: artifact regeneration, end-to-end validation, and product completion.
