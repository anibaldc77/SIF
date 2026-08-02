---
id: EG-345
title: Application Skeleton Architecture and First-Run Model
summary: Defines the governed architecture, canonical project structure, manifest boundary, generation policy, secret handling, cross-platform requirements and idempotent first-run model for applications created with SIF.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - application
  - skeleton
  - scaffolding
  - first-run
  - installer
  - cli
  - configuration
  - modules
  - architecture
depends_on:
  - EG-304
  - EG-320
  - EG-328
  - EG-336
  - EG-344
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-345 — Application Skeleton Architecture and First-Run Model

## 1. Purpose

WP-222 establishes the governed application skeleton, project scaffolding and first-run experience for applications built with SIF.

The subsystem SHALL convert the framework capabilities completed through WP-221 into a reproducible application project that can be created, inspected, configured and started without copying the framework repository or exposing internal Foundation implementation details.

The skeleton SHALL remain a consumer of SIF. It SHALL NOT duplicate framework source code, bypass Composer dependency management, embed mutable framework state or infer operational authorization from interactive prompts.

## 2. Architectural objectives

WP-222 SHALL provide:

1. a canonical application directory structure;
2. an immutable project manifest and project identity model;
3. deterministic generation of directories and governed files;
4. explicit ownership of generated, user-owned and runtime-generated paths;
5. Composer-based separation between framework and application;
6. bootstrap entry points for CLI and future HTTP execution;
7. environment-aware configuration without committing secrets;
8. integration with the Developer CLI through an explicit `app:create` workflow;
9. templates for modules, migrations and BaseModel 2.0 models;
10. validation of an application before first execution;
11. an idempotent, resumable first-run workflow;
12. a minimal example application that exercises the supported composition path;
13. Windows and Unix-compatible launchers and paths;
14. an update policy that never overwrites user-owned code silently;
15. runtime integration without eager database or filesystem mutation during bootstrap.

## 3. Dependency direction

The mandatory dependency direction is:

```text
Generated SIF application
        ↓
Application bootstrap and configuration
        ↓
SIF public contracts and runtimes
        ↓
Concrete adapters selected by the application
```

The following direction is prohibited:

```text
SIF Foundation -> generated application classes
```

The framework SHALL NOT depend on a particular generated application.

## 4. Canonical application structure

A newly generated application SHALL use the following baseline structure:

```text
<project-root>/
├── app/
│   ├── Commands/
│   ├── Models/
│   ├── Modules/
│   └── Providers/
├── bootstrap/
│   ├── app.php
│   └── cli.php
├── config/
│   ├── app.php
│   ├── database.php
│   ├── logging.php
│   └── modules.php
├── database/
│   └── migrations/
├── public/
│   └── index.php
├── resources/
├── routes/
├── storage/
│   ├── cache/
│   ├── logs/
│   └── runtime/
├── tests/
├── .env.example
├── .gitignore
├── composer.json
├── phpunit.xml
├── sif.project.json
├── sif
└── sif.bat
```

Directories MAY remain empty when no capability requires content, but their ownership and purpose SHALL be defined by the project manifest or skeleton specification.

## 5. Path ownership model

Every generated path SHALL belong to exactly one ownership class.

### 5.1 Skeleton-owned

Skeleton-owned files are generated initially and MAY receive governed updates only through an explicit upgrade plan.

Examples:

- launcher scripts;
- baseline bootstrap files;
- `.env.example`;
- default configuration stubs;
- project manifest schema version.

### 5.2 User-owned

User-owned files SHALL never be overwritten automatically after creation.

Examples:

- application models;
- modules;
- commands;
- routes;
- application providers;
- application tests.

### 5.3 Runtime-owned

Runtime-owned paths contain generated operational state and SHALL be excluded from version control unless explicitly documented otherwise.

Examples:

- logs;
- caches;
- runtime journals;
- local lock state;
- temporary generated artifacts.

## 6. Project manifest boundary

Every generated application SHALL contain:

```text
sif.project.json
```

The manifest SHALL identify at least:

- manifest schema version;
- project identifier;
- display name;
- application namespace;
- minimum PHP version;
- required SIF version constraint;
- skeleton version;
- enabled entry points;
- configured environment names;
- path ownership declarations;
- optional capability declarations.

The manifest SHALL NOT contain:

- passwords;
- tokens;
- private keys;
- database credentials;
- absolute paths that make the project non-portable;
- machine-specific temporary state.

The immutable value model for this manifest is reserved for I2.

## 7. Generation policy

Scaffolding SHALL be deterministic for equivalent input and skeleton version.

Before any mutation, the generator SHALL produce a plan containing:

- directories to create;
- files to create;
- files that already exist;
- conflicts;
- skipped optional artifacts;
- expected fingerprints for governed templates.

Generation SHALL be fail-closed when:

- the target path is not authorized;
- the target is non-empty and no explicit compatible policy was selected;
- a user-owned file would be overwritten;
- the manifest is invalid;
- the requested namespace or project identifier is invalid;
- required runtime capabilities are unavailable.

Interactive confirmation SHALL NOT replace target authorization or overwrite policy.

## 8. Overwrite and upgrade policy

The baseline overwrite policies SHALL be:

```text
fail       reject any conflicting path
skip       retain existing compatible files
replace    replace only explicitly authorized skeleton-owned files
```

`replace` SHALL NOT apply to user-owned files.

Skeleton upgrades SHALL be represented as explicit plans with before/after fingerprints. An upgrade SHALL be independently reviewable and SHALL support dry-run before mutation.

## 9. Bootstrap architecture

The generated application SHALL provide separate bootstrap boundaries:

```text
bootstrap/app.php
bootstrap/cli.php
```

`bootstrap/app.php` SHALL compose the application runtime using public SIF contracts.

`bootstrap/cli.php` SHALL return a `CliRuntime` suitable for `bin/sif` or the project launchers.

Bootstrap SHALL NOT automatically:

- execute migrations;
- run Installer mutations;
- create database structures;
- publish resources;
- clear caches;
- write secrets;
- execute first-run actions.

Operational mutations remain explicit CLI or Installer operations.

## 10. Configuration and environments

The skeleton SHALL distinguish:

- committed configuration defaults;
- environment-specific overrides;
- local secrets;
- runtime-generated configuration caches.

`.env.example` MAY document variable names and safe example values. A real `.env` file SHALL be ignored by Git by default.

Supported environment names SHALL be declared explicitly, with an initial baseline of:

```text
local
testing
production
```

The generator SHALL NOT create production credentials or guess database connection values.

## 11. Secret handling

The following files and paths SHALL be ignored by default:

```text
.env
.env.*.local
storage/cache/*
storage/logs/*
storage/runtime/*
```

Placeholder files MAY preserve empty runtime directories when required.

No generated diagnostic, manifest, README or command output SHALL print secret values.

## 12. First-run model

First-run SHALL be an explicit state machine, not an implicit bootstrap side effect.

The baseline stages SHALL be:

```text
uninitialized
validated
configured
planned
authorized
executed
completed
failed
```

A first-run operation SHALL:

1. validate the project manifest;
2. validate PHP and required extensions;
3. validate writable runtime paths;
4. validate configuration schema;
5. assess Installer requirements;
6. generate a dry-run plan;
7. require explicit execution authorization;
8. execute governed mutations;
9. record a result or journal;
10. support safe repetition after successful completion.

A repeated first-run invocation SHALL report the completed state and SHALL NOT reapply mutations unless an explicit new plan requires them.

## 13. CLI integration

WP-222 SHALL extend the WP-221 CLI with an application creation workflow conceptually equivalent to:

```text
sif app:create <path> --name=<name> --namespace=<namespace>
```

The final syntax is reserved for I5.

The command SHALL support:

- dry-run;
- non-interactive execution;
- structured JSON output;
- explicit target authorization;
- explicit overwrite policy;
- deterministic result reporting.

The command SHALL NOT download arbitrary code or execute generated project code during scaffolding.

## 14. Templates

Templates SHALL be versioned resources owned by the scaffolding subsystem.

Template rendering SHALL use validated placeholders rather than arbitrary PHP evaluation.

Initial template families SHALL include:

- application bootstrap;
- CLI bootstrap;
- configuration files;
- Composer manifest;
- module class and provider;
- migration class;
- BaseModel 2.0 model and metadata factory;
- PHPUnit bootstrap and example test.

Template generation for modules, migrations and models is reserved for I6.

## 15. Cross-platform requirements

Generated projects SHALL work on:

- Windows with PowerShell or Command Prompt launchers;
- Linux/macOS with PHP CLI and POSIX-compatible shell execution.

All internal manifest paths SHALL use `/` as the portable logical separator. Physical paths SHALL be resolved through path abstractions and SHALL NOT be assembled by unchecked string concatenation.

Generated text files SHALL use UTF-8 without BOM. Scripts MAY use platform-appropriate line endings where required, but tests SHALL compare output using platform-independent expectations.

## 16. Validation and example application

WP-222 SHALL include an application validator that checks at least:

- manifest schema and version;
- required files;
- required directories;
- Composer metadata;
- namespace/path compatibility;
- bootstrap return types;
- writable runtime paths;
- absence of known secret files from tracked skeleton output;
- compatibility with the installed SIF version.

I7 SHALL provide a minimal example application that can execute at least:

```text
runtime:about
runtime:doctor
config:validate
```

without requiring a database.

## 17. Compatibility and evolution

The skeleton version SHALL evolve independently from the framework version, while declaring a compatible SIF version constraint.

Generated applications SHALL remain valid when the framework receives compatible minor or patch releases.

Breaking skeleton changes SHALL require:

- a new skeleton major version;
- an explicit migration plan;
- a dry-run report;
- no silent replacement of user-owned files.

## 18. Eight-increment delivery sequence

WP-222 SHALL be delivered as:

```text
I1 — Application skeleton architecture and first-run model
I2 — Immutable project manifest and skeleton value model
I3 — Deterministic project structure and file generation
I4 — Bootstrap, configuration and environment templates
I5 — CLI integration and app:create orchestration
I6 — Module, migration and BaseModel template generation
I7 — Validation, first-run workflow and example application
I8 — Runtime integration, documentation and product completion
```

## 19. Acceptance criteria for I1

I1 is accepted when:

1. the application/framework dependency direction is explicit;
2. the canonical project structure is documented;
3. path ownership classes are defined;
4. the project manifest boundary is defined;
5. generation and overwrite policies are fail-closed;
6. secrets and runtime state are excluded appropriately;
7. bootstrap and first-run are separated;
8. first-run is idempotent and authorization-aware;
9. cross-platform behavior is defined;
10. the eight-increment sequence is approved.
