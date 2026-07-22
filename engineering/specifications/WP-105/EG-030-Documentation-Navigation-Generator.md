# EG-030 — Documentation Navigation Generator

- **Work package:** WP-105
- **Increment:** 5
- **Status:** Implemented
- **Version:** 1.0.0

## 1. Purpose

Define the built-in `documentation.navigation` generator, responsible for producing a stable human-oriented entry point to governed engineering documentation.

## 2. Output

The generator produces exactly one artifact:

```text
engineering/NAVIGATION.generated.md
```

The artifact is generated content and must not be manually edited.

## 3. Scope

The navigation document:

- lists all governed documents exactly once;
- groups documents by category and optional work package;
- provides relative links from the generated artifact;
- displays identifier, title, document type, status, and version;
- provides shortcuts to the other built-in generated views;
- remains deterministic and portable across Windows and Unix paths.

It does not replace `repository.index`. The repository index is an inventory and status summary; navigation is a concise reading entry point.

## 4. Inputs

Required:

- `RepositoryWorkspace`;
- `RepositoryIndex`.

Reference resolution is not required.

## 5. Determinism

Groups are ordered by normalized category and work package. Documents inside groups are ordered naturally by identifier and then path. The output contains no timestamps, absolute paths, random values, or runtime identifiers.

## 6. Diagnostics

`GENERATOR-105` is returned when the repository workspace or index is unavailable.

## 7. Registration

`DefaultCliApplicationFactory` registers `documentation.navigation` in the normal `GeneratorRegistry`. It is not a CLI special case.

## 8. Verification

Tests cover view construction, Markdown rendering, generator behavior, missing input diagnostics, and CLI catalog registration.
