# EG-043 — Repository Policy Configuration

## Status
Implemented

## Objective
Convert the declarative `repository_policies` section of `.sif/builder.json` into the immutable `RepositoryPolicySet` consumed by `RepositoryPolicyAnalyzer`.

## Security boundary
Configuration never names PHP classes. Policy types are resolved only through a registered `RepositoryPolicyFactoryCatalog`. Factories receive validated arrays and create known rule implementations.

## Built-in policy types
- `required.category`
- `required.metadata`

Each declaration requires its own `id`. Supported severities are `info`, `warning`, `error`, and `fatal`; the default is `error`.

## Example
```json
{
  "repository_policies": {
    "required.category": [
      {
        "id": "repository.architecture",
        "category": "architecture",
        "severity": "warning"
      }
    ],
    "required.metadata": [
      {
        "id": "repository.approval",
        "field": "approved_by",
        "status": "approved"
      }
    ]
  }
}
```

## Validation
Unknown policy types, unknown parameters, missing or invalid values, invalid severities, and duplicate rule identifiers produce `CONFIG-112`. Diagnostics are accumulated; no partial policy set is returned.

## Determinism
Factory identifiers and resulting policy rules are ordered lexically by their governed identifiers. No timestamps, environment interpolation, remote access, or dynamic class loading are used.
