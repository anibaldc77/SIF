---
id: ES-002
title: Metadata Standard
status: Draft for Review
version: 0.1.0
category: Engineering Standard
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-16
updated: 2026-07-16
tags:
  - metadata
  - yaml
  - documentation
  - builder
work_package: WP-100
depends_on:
  - ES-001
related_adrs: []
supersedes: null
superseded_by: null
---

# ES-002 — Metadata Standard

## Executive Summary

This Standard defines the structured metadata model used by official SIF engineering artifacts.

Metadata is represented as YAML Front Matter placed at the beginning of each governed Markdown document. The model enables deterministic identification, classification, lifecycle management, dependency analysis, traceability, schema validation and automated processing by SIF Builder.

This document is normative.

## 1. Purpose

ES-002 establishes:

- the canonical YAML Front Matter representation;
- mandatory and optional metadata fields;
- allowed value types and enumerations;
- identifier, version and date constraints;
- dependency and traceability relationships;
- validation and conformance requirements;
- compatibility rules for metadata evolution.

## 2. Scope

This Standard applies to official SIF engineering artifacts, including:

- Constitution;
- Architecture Specifications;
- Engineering Standards;
- Policies;
- Architecture Decision Records;
- Requests for Comments;
- Work Package documents;
- normative specifications;
- architecture and implementation reviews;
- official templates.

It does not require Front Matter for:

- source-code files;
- examples and tutorials;
- temporary notes;
- meeting minutes;
- generated files whose source artifact already contains compliant metadata.

## 3. Normative References

- SIF Constitution
- SIF Architecture Specification
- ES-001 — Engineering Documentation Standard
- ES-003 — Document Class Model, when approved
- ES-005 — Versioning Standard, when approved

## 4. Terminology

### 4.1 Engineering Artifact

A version-controlled document governed by SIF engineering standards.

### 4.2 Front Matter

A YAML mapping delimited by `---` lines and placed before the first Markdown heading.

### 4.3 Canonical Identifier

The permanent, globally unique identifier of one Engineering Artifact.

### 4.4 Reference Identifier

A canonical identifier used to express a dependency or traceability relationship.

### 4.5 Null Value

The YAML `null` value. Empty strings SHALL NOT be used as substitutes for null values.

## 5. Front Matter Representation

### MD-001 — First Content

YAML Front Matter SHALL be the first content in every governed document. No byte-order mark, heading, comment or blank prose SHALL precede the opening delimiter.

### MD-002 — Delimiters

Front Matter SHALL begin and end with a line containing exactly:

```yaml
---
```

### MD-003 — YAML Mapping

The Front Matter root SHALL be a YAML mapping. A sequence or scalar root is prohibited.

### MD-004 — UTF-8

Metadata and Markdown content SHALL be encoded as UTF-8.

### MD-005 — Stable Keys

Metadata field names SHALL use lowercase `snake_case` ASCII identifiers.

## 6. Core Metadata Schema

The following fields define the Core Metadata Schema.

| Field | Type | Required | Null Allowed | Purpose |
|---|---|:---:|:---:|---|
| `id` | string | Yes | No | Permanent canonical identifier |
| `title` | string | Yes | No | Human-readable document title |
| `status` | enum | Yes | No | Lifecycle state |
| `version` | string | Yes | No | Semantic document version |
| `category` | enum | Yes | No | Artifact category |
| `document_class` | enum | Conditional | No | Validation class defined by ES-003 |
| `authors` | sequence of strings | Yes | No | Responsible authors or board |
| `created` | date | Yes | No | Initial creation date |
| `updated` | date | Yes | No | Last substantive update date |
| `tags` | sequence of strings | Yes | No | Search and classification tags |
| `work_package` | string or null | Conditional | Yes | Owning Work Package |
| `depends_on` | sequence of strings | Yes | No | Normative dependencies |
| `related_adrs` | sequence of strings | Yes | No | Related architecture decisions |
| `supersedes` | string or null | No | Yes | Artifact directly superseded |
| `superseded_by` | string or null | No | Yes | Artifact that supersedes this one |

A document class MAY define additional mandatory fields. It SHALL NOT weaken Core Metadata requirements.

## 7. Field Requirements

### 7.1 `id`

#### ID-001 — Presence

Every governed artifact SHALL define exactly one `id`.

#### ID-002 — Immutability

The canonical identifier SHALL NOT change after the artifact is first committed, except to correct an identifier collision before approval.

#### ID-003 — Uniqueness

No two active or historical artifacts SHALL share the same canonical identifier.

#### ID-004 — Format

The identifier SHALL:

- contain only uppercase ASCII letters, digits and hyphens;
- begin with an uppercase letter;
- not begin or end with a hyphen;
- not contain consecutive hyphens.

Examples:

```text
ES-002
ADR-0009
WP-004-05
REVIEW-WP-004
```

### 7.2 `title`

The title SHALL be a non-empty human-readable string and SHOULD match the primary Markdown heading without the identifier prefix.

### 7.3 `status`

Allowed lifecycle values are:

- `Draft`
- `Draft for Review`
- `Technical Review`
- `Release Candidate`
- `Approved`
- `Deprecated`
- `Superseded`
- `Archived`

Status comparisons are case-sensitive.

A document SHALL NOT use an unregistered status value.

### 7.4 `version`

The version SHALL conform to Semantic Versioning 2.0.0 syntax.

Valid examples:

```text
0.1.0
1.0.0
1.0.0-rc.1
2.4.3
```

Build metadata SHOULD NOT be used for official artifact versions.

### 7.5 `category`

Allowed Core categories are:

- `Constitution`
- `Architecture Specification`
- `Engineering Standard`
- `Policy`
- `Architecture Decision Record`
- `Request for Comments`
- `Work Package`
- `Normative Specification`
- `Architecture Review`
- `Implementation Review`
- `Informative Document`
- `Template`

New categories require an approved update to this Standard or to ES-003.

### 7.6 `document_class`

When ES-003 is approved, every governed artifact SHALL identify one registered document class.

Until ES-003 is approved, the field MAY be omitted from legacy artifacts but SHOULD be included in new artifacts.

Initial reserved values are:

- `NormativeDocument`
- `GovernanceDocument`
- `ReviewDocument`
- `InformativeDocument`
- `TemplateDocument`

### 7.7 `authors`

`authors` SHALL be a non-empty YAML sequence.

Each entry SHALL identify a person, team or recognized governing body. A comma-separated scalar is prohibited.

### 7.8 Dates

`created` and `updated` SHALL use ISO 8601 calendar date format:

```text
YYYY-MM-DD
```

`updated` SHALL NOT precede `created`.

Editorial changes MAY update `updated` without changing the semantic version when they do not alter meaning.

### 7.9 `tags`

`tags` SHALL be a YAML sequence. Tags SHALL use lowercase ASCII `kebab-case`.

Duplicate tags are prohibited.

### 7.10 `work_package`

Artifacts owned by a Work Package SHALL define its canonical identifier, such as `WP-004` or `WP-100`.

Cross-project governance artifacts MAY use `null`.

### 7.11 `depends_on`

`depends_on` SHALL list canonical identifiers of artifacts required for normative interpretation.

The sequence MAY be empty. Missing dependencies are prohibited.

An artifact SHALL NOT directly depend on itself.

### 7.12 `related_adrs`

`related_adrs` SHALL list canonical ADR identifiers materially related to the artifact.

The sequence MAY be empty. A narrative title SHALL NOT replace the ADR identifier.

### 7.13 Supersession

`supersedes` and `superseded_by` express direct replacement relationships.

An artifact with status `Superseded` SHALL define `superseded_by`.

The successor SHOULD define the predecessor through `supersedes`.

Circular supersession relationships are prohibited.

## 8. Ordering

Core metadata fields SHOULD appear in the following order:

```yaml
id:
title:
status:
version:
category:
document_class:
authors:
created:
updated:
tags:
work_package:
depends_on:
related_adrs:
supersedes:
superseded_by:
```

Additional class-specific fields SHALL appear after Core fields.

Ordering is not semantically significant but improves readability and deterministic generation.

## 9. Reference Integrity

### REF-001 — Canonical References

Metadata relationships SHALL use canonical identifiers rather than filenames, relative paths or document titles.

### REF-002 — Existing Targets

Every non-null reference SHALL resolve to an existing or explicitly reserved artifact identifier.

### REF-003 — No Duplicate References

Sequences of references SHALL NOT contain duplicates.

### REF-004 — Dependency Cycles

Direct self-dependencies are prohibited. Cyclic dependency graphs across normative artifacts SHOULD be rejected unless explicitly permitted by an approved architecture decision.

### REF-005 — Historical Integrity

Archived, Deprecated and Superseded artifacts SHALL retain their canonical identifiers so historical references remain resolvable.

## 10. Lifecycle Consistency

### LC-001 — Draft Versions

A Draft artifact SHOULD use a `0.x.y` version until its first Release Candidate unless an existing stable artifact is undergoing revision.

### LC-002 — Release Candidate Versions

A Release Candidate SHALL use a prerelease suffix such as `1.0.0-rc.1`.

### LC-003 — Approved Versions

An Approved artifact SHALL use a stable Semantic Version without a prerelease suffix.

### LC-004 — Superseded Artifacts

An artifact with status `Superseded` SHALL define `superseded_by` and SHALL NOT receive new normative changes.

### LC-005 — Archived Artifacts

Archived artifacts are retained for historical purposes and SHALL NOT be treated as active normative sources.

## 11. Extension Metadata

Document classes MAY add metadata fields when Core Metadata is insufficient.

Extension fields SHALL:

- use lowercase `snake_case`;
- be documented by the applicable Standard;
- have a defined type and validation rule;
- not duplicate or contradict a Core field;
- remain deterministic and machine-readable.

Implementation-specific, temporary or secret values SHALL NOT be stored in official metadata.

## 12. Security and Privacy

Metadata SHALL NOT contain:

- credentials;
- secrets;
- private keys;
- access tokens;
- personal contact information not required by governance;
- confidential implementation details unrelated to document discovery and traceability.

References to confidential artifacts MAY use approved identifiers without embedding confidential content.

## 13. Validation

### VAL-001 — Schema Validation

Metadata SHALL be validated against the official SIF metadata schema when that schema becomes available.

### VAL-002 — Semantic Validation

Schema validation alone is insufficient. Tooling SHALL additionally validate:

- identifier uniqueness;
- reference integrity;
- lifecycle and version consistency;
- supersession relationships;
- category and document-class compatibility;
- date ordering.

### VAL-003 — Deterministic Results

Equivalent metadata inputs SHALL produce equivalent validation results and diagnostic ordering.

### VAL-004 — Typed Diagnostics

Validation diagnostics SHALL identify:

- artifact identifier or file;
- metadata field;
- rule identifier;
- severity;
- actionable message.

### VAL-005 — No Silent Correction

Validation tools SHALL NOT silently modify invalid metadata. Automatic correction requires an explicit formatting or migration operation.

## 14. Conformance

An Engineering Artifact conforms to ES-002 when:

- valid YAML Front Matter is the first content;
- every mandatory Core field exists;
- field values satisfy the type and format rules;
- lifecycle and version are consistent;
- references are canonical and resolvable;
- no prohibited metadata is present;
- class-specific metadata requirements are satisfied when applicable.

A tool conforms to ES-002 when it:

- parses compliant Front Matter;
- rejects invalid mandatory metadata;
- preserves unknown documented extension fields;
- performs the semantic validations applicable to its declared conformance level;
- produces deterministic diagnostics.

## 15. Compatibility and Evolution

The names and meanings of Core fields are compatibility protected after ES-002 reaches Approved status.

Adding an optional field is backward compatible.

Making an optional field mandatory, removing a field, changing a field type or changing the meaning of an existing value is a breaking change.

Breaking changes require:

- an approved ADR;
- a major version increment;
- a migration strategy;
- coordinated schema and Builder updates.

## 16. Traceability

### Applies To

- WP-100
- All official SIF Engineering Artifacts

### Affected Components

- Engineering Standards
- ADR infrastructure
- Work Package specifications
- Architecture and implementation reviews
- SIF Builder Metadata Parser
- SIF Builder Schema Validator
- Documentation Indexer
- Traceability Analyzer

### Derived Artifacts

- `engineering/schemas/metadata.schema.json`
- document-class schemas
- generated indexes
- metadata validation reports

## 17. Acceptance Criteria

ES-002 is ready for Release Candidate when:

- Core fields are fully defined;
- lifecycle, category and class enumerations are explicit;
- identifier and version formats are unambiguous;
- reference and supersession rules are complete;
- validation and conformance requirements are testable;
- at least one representative artifact from each initial document class can be expressed without undocumented fields;
- no unresolved architectural decision remains.

## Rule Index

| Rule | Title |
|---|---|
| MD-001 | First Content |
| MD-002 | Delimiters |
| MD-003 | YAML Mapping |
| MD-004 | UTF-8 |
| MD-005 | Stable Keys |
| ID-001 | Identifier Presence |
| ID-002 | Identifier Immutability |
| ID-003 | Identifier Uniqueness |
| ID-004 | Identifier Format |
| REF-001 | Canonical References |
| REF-002 | Existing Targets |
| REF-003 | No Duplicate References |
| REF-004 | Dependency Cycles |
| REF-005 | Historical Integrity |
| LC-001 | Draft Versions |
| LC-002 | Release Candidate Versions |
| LC-003 | Approved Versions |
| LC-004 | Superseded Artifacts |
| LC-005 | Archived Artifacts |
| VAL-001 | Schema Validation |
| VAL-002 | Semantic Validation |
| VAL-003 | Deterministic Results |
| VAL-004 | Typed Diagnostics |
| VAL-005 | No Silent Correction |

## Revision History

| Version | Date | Status | Description |
|---|---|---|---|
| 0.1.0 | 2026-07-16 | Draft for Review | Initial metadata model, field requirements, lifecycle consistency, validation, conformance and compatibility rules. |
