---
id: BUILD-PROFILES
title: Build Profiles and Repository Configuration
summary: SIF Builder reads repository configuration from .sif/builder.json unless a different file is selected with --configuration=<path>.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - build
  - profiles
  - repository
  - configuration
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Build Profiles and Repository Configuration

SIF Builder reads repository configuration from `.sif/builder.json` unless a different file is selected with `--configuration=<path>`.

## Resolution order

Configuration is resolved in the following order:

1. built-in defaults;
2. repository configuration;
3. selected build profile;
4. inherited profile values;
5. explicit CLI options.

Explicit CLI selections replace the corresponding profile values.

## Selecting a profile

```powershell
php bin\sif-builder build --profile=development
php bin\sif-builder validate --profile=ci
```

When `--profile` is omitted, the configuration field `default_profile` is used. If no configuration file exists, the built-in default behavior remains available.

## Selecting a configuration file

```powershell
php bin\sif-builder build --configuration=.sif/builder.json
```

The path is resolved from the repository root supplied to the command.

## CLI precedence

The following command uses the selected profile but replaces its generator selection, execution mode and reporter format:

```powershell
php bin\sif-builder build `
  --profile=development `
  --generator=repository.index `
  --lenient `
  --format=json
```

## Profile inheritance

Profiles may extend another profile:

```json
{
  "profiles": {
    "base": {
      "analyzers": ["metadata.completeness"],
      "generators": [],
      "reporters": ["report.markdown"],
      "execution": {"strict": false}
    },
    "ci": {
      "extends": "base",
      "execution": {"strict": true}
    }
  }
}
```

Circular inheritance, missing parent profiles and unknown extensions are rejected before Builder Engine execution.

## Repository policies

Built-in configurable policy types are:

- `required.category`;
- `required.metadata`.

Each configured rule receives a stable identifier and severity. Policies are supplied to the `repository.policy` analyzer through the resolved CLI configuration.

## Diagnostics

Configuration failures use `CONFIG-*` diagnostics. In particular, an unknown configured extension produces `CONFIG-110` and maps to the CLI invalid-usage exit code.

## Complete example

A complete configuration is available at:

```text
tools/builder/examples/builder.json
```
