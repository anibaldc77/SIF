---
id: EG-039
title: Build Profiles and Repository Configuration
status: Draft
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-107
summary: Architecture for validated repository configuration and deterministic build profiles.
tags:
  - builder
  - configuration
  - profiles
  - governance
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
depends_on: []
related_adrs: []
---

# EG-039 — Build Profiles and Repository Configuration

## 1. Purpose

WP-107 introduces repository-owned, validated and deterministic configuration for SIF Builder. It replaces hard-coded default composition as the only execution mode while preserving the current default behavior when no configuration file exists.

The work package defines build profiles that select analyzers, generators, reporters, repository policies and execution policy without coupling the Engine to YAML, JSON or the CLI.

## 2. Problem statement

The Builder currently exposes governed extension catalogs, but the default CLI composition decides globally which extensions run. This creates five limitations:

1. every repository receives the same analyzer and generator set;
2. institutional policies cannot be declared by repository configuration;
3. strictness cannot vary by workflow;
4. CI, local development and release builds cannot use named profiles;
5. configuration validation is not represented as a first-class deterministic result.

WP-107 solves these limitations without changing extension contracts implemented in WP-100 through WP-106.

## 3. Scope

WP-107 includes:

- repository configuration discovery;
- a format-neutral configuration model;
- schema and semantic validation;
- named build profiles;
- deterministic profile inheritance;
- extension selection by governed identifiers;
- repository-policy composition;
- execution policy selection;
- CLI profile selection;
- diagnostics and end-to-end validation.

## 4. Out of scope

WP-107 does not include:

- remote configuration;
- environment-variable interpolation;
- executable PHP configuration files;
- arbitrary service-container definitions;
- plugin installation;
- secret management;
- conditional expressions;
- mutation of configuration by the Builder;
- replacement of existing Engine extension contracts.

## 5. Configuration location

The governed repository configuration file is:

```text
.sif/builder.json
```

JSON is selected for the first implementation because it is deterministic, supported by PHP without an additional dependency and compatible with schema-style validation.

If the file does not exist, the Builder uses the backward-compatible built-in default profile.

## 6. Conceptual configuration

```json
{
  "schema_version": "1.0",
  "default_profile": "development",
  "profiles": {
    "base": {
      "analyzers": [
        "metadata.completeness",
        "reference.integrity",
        "document.consistency"
      ],
      "generators": [],
      "reporters": ["report.markdown"],
      "execution": {
        "strict": false
      }
    },
    "development": {
      "extends": "base",
      "analyzers": ["repository.policy", "generated.artifacts"]
    },
    "release": {
      "extends": "development",
      "generators": [
        "repository.index",
        "reference.report",
        "reference.graph",
        "repository.manifest",
        "documentation.navigation"
      ],
      "reporters": ["report.markdown", "report.json"],
      "execution": {
        "strict": true
      }
    }
  },
  "repository_policies": {
    "release": [
      {
        "type": "required_category",
        "id": "repository.constitution",
        "category": "Constitution"
      }
    ]
  }
}
```

The exact serialized schema will be frozen during Increment 1. The example defines intent and boundaries, not an implementation shortcut.

## 7. Architecture

### 7.1 Configuration layer

```text
tools/builder/src/Configuration/
    RepositoryConfiguration.php
    RepositoryConfigurationLoaderInterface.php
    JsonRepositoryConfigurationLoader.php
    RepositoryConfigurationValidator.php
    ConfigurationDiagnostic.php
    Exception/
```

Responsibilities:

- locate and read `.sif/builder.json`;
- decode JSON with explicit errors;
- normalize values into immutable objects;
- produce diagnostics instead of leaking parser exceptions;
- preserve source location where available.

### 7.2 Profile model

```text
tools/builder/src/Profile/
    BuildProfile.php
    BuildProfileCollection.php
    BuildProfileResolver.php
    ResolvedBuildProfile.php
    ExtensionSelection.php
    ExecutionPolicyConfiguration.php
    Exception/
```

A resolved profile is immutable and contains ordered, duplicate-free identifiers for:

- analyzers;
- generators;
- reporters;
- repository policy rules;
- execution behavior.

### 7.3 Composition

```text
tools/builder/src/Cli/Runtime/
    ConfiguredCliApplicationFactory.php
    DefaultCliApplicationFactory.php
```

`DefaultCliApplicationFactory` remains the backward-compatible composition root.

`ConfiguredCliApplicationFactory` decorates or delegates to governed catalogs and builds the application from a `ResolvedBuildProfile`.

The Engine must not parse files or know profile names.

### 7.4 Catalog validation

Every configured extension identifier must exist in its governed catalog.

Unknown identifiers produce configuration diagnostics and stop execution before repository discovery.

No silent fallback is allowed for misspelled identifiers.

## 8. Profile inheritance

A profile may extend at most one other profile.

Resolution rules:

1. parent is resolved first;
2. child extension lists append to inherited lists;
3. duplicates are removed while preserving first occurrence;
4. scalar execution settings override parent values;
5. repository policies append deterministically;
6. inheritance cycles are invalid;
7. missing parents are invalid.

Multiple inheritance is excluded to avoid ambiguous precedence.

## 9. CLI behavior

Planned options:

```text
--profile=<name>
--config=<path>
```

Precedence:

1. explicit `--profile`;
2. configuration `default_profile`;
3. built-in `default` profile.

`--config` is intended for controlled CI/testing use. The default remains `.sif/builder.json` under the repository root.

Planned command:

```text
config:validate
```

It validates configuration and profile resolution without running discovery, analyzers or generators.

## 10. Diagnostics

Reserved configuration diagnostic range:

```text
CONFIG-100..199
```

Initial allocation:

| Code | Meaning |
|---|---|
| `CONFIG-101` | Configuration file cannot be read |
| `CONFIG-102` | Invalid JSON |
| `CONFIG-103` | Unsupported schema version |
| `CONFIG-104` | Required configuration field missing |
| `CONFIG-105` | Invalid value type or value |
| `CONFIG-106` | Unknown profile |
| `CONFIG-107` | Missing inherited profile |
| `CONFIG-108` | Profile inheritance cycle |
| `CONFIG-109` | Unknown analyzer identifier |
| `CONFIG-110` | Unknown generator identifier |
| `CONFIG-111` | Unknown reporter identifier |
| `CONFIG-112` | Invalid repository policy declaration |
| `CONFIG-113` | Duplicate profile identifier |

All diagnostics must have deterministic ordering, structured context and remediation.

## 11. Security requirements

- Configuration is data, never executable code.
- Paths are normalized and constrained to the repository unless explicitly allowed by a future ADR.
- No environment-variable expansion is performed in WP-107.
- Unknown keys are rejected unless the schema explicitly marks them as extensible.
- Parser errors must not expose secrets or unrelated filesystem content.

## 12. Compatibility requirements

When `.sif/builder.json` is absent:

- the current CLI catalog remains unchanged;
- all five built-in analyzers remain registered;
- all five built-in generators remain registered;
- both reporters remain registered;
- existing commands and tests retain their behavior.

WP-107 must not modify public extension interfaces unless accompanied by an explicit migration plan.

## 13. Planned increments

### Increment 1 — Configuration Model and JSON Loader

- immutable configuration objects;
- `.sif/builder.json` loader;
- parser and schema-version diagnostics;
- unit tests.

### Increment 2 — Build Profile Model and Resolver

- named profiles;
- single inheritance;
- deterministic merge;
- cycle and missing-parent detection;
- unit tests.

### Increment 3 — Extension Catalog Validation

- analyzer, generator and reporter identifier validation;
- unknown-extension diagnostics;
- resolved extension selection.

### Increment 4 — Repository Policy Configuration

- declarative mapping to existing `RepositoryPolicyRuleInterface` implementations;
- policy factory contracts;
- no arbitrary class names in configuration.

### Increment 5 — CLI Profile Integration

- `--profile` and `--config`;
- configured application composition;
- `config:validate` command;
- backward-compatible defaults.

### Increment 6 — End-to-End Validation and Closure

- development and release profile fixtures;
- invalid configuration scenarios;
- strict/non-strict behavior;
- generator selection;
- complete suite and PHPStan validation.

## 14. Acceptance criteria

WP-107 is complete when:

1. a repository can declare named profiles in `.sif/builder.json`;
2. profiles resolve deterministically;
3. invalid configuration produces governed diagnostics;
4. configured extension identifiers are validated against catalogs;
5. repository policies are composed only through registered policy factories;
6. CLI profile selection works;
7. absence of configuration preserves current behavior;
8. all tests, Composer validation and PHPStan level 8 pass;
9. end-to-end tests prove profile-specific analyzer and generator execution.

## 15. Architectural decision summary

WP-107 adopts repository-owned declarative JSON configuration, immutable profile resolution and catalog-validated composition. It deliberately keeps parsing and profile selection outside the Engine, ensuring that the Builder core remains format-neutral and extension-oriented.
