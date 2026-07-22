# EG-029 — Repository Manifest Generator

**Work Package:** WP-105  
**Status:** Approved for implementation  
**Version:** 1.0.0  
**Generator:** `repository.manifest`

## 1. Purpose

Define the built-in generator that publishes a deterministic, machine-readable manifest of the engineering repository. The manifest is intended for CI/CD, repository audits, release tooling, and future SDK consumers.

## 2. Artifact contract

The generator emits exactly one artifact:

```text
build/repository-manifest.generated.json
```

The root JSON object contains:

- `schema_version`
- generator provenance
- integrity capability declaration
- repository summary
- ordered document inventory
- ordered resolved and broken references

Schema version starts at `1.0.0` and is independent from the SIF Builder package version.

## 3. Inputs

The generator requires a `RepositoryWorkspace` containing:

1. `RepositoryIndex`
2. `ResolutionResult`

Missing input produces diagnostic `GENERATOR-104`. Expected absence does not raise an exception.

## 4. Document entries

Each indexed document includes:

- identifier, title, type, category, status, and version
- normalized repository-relative path
- work package and sorted tags
- incoming, outgoing, and broken reference counts
- deterministic `entry_fingerprint`

### 4.1 Fingerprint semantics

`entry_fingerprint` is SHA-256 over normalized indexed metadata. It is **not** a hash of file bytes. The manifest declares:

```json
{
  "content_hashes_available": false,
  "entry_fingerprint_scope": "normalized_index_metadata"
}
```

A future content hashing capability requires a dedicated workspace contract or hashing service and a schema evolution decision.

## 5. References

Resolved and broken references share one ordered collection. Each entry exposes source, target, type, source line, resolution state, and optional failure reason.

## 6. Determinism

The generator MUST:

- use repository-relative forward-slash paths
- sort documents naturally by identifier
- sort tags naturally
- sort references by source, target, type, line, state, and reason
- sort summary maps by key
- exclude timestamps, absolute paths, run identifiers, and random values
- render UTF-8 JSON with exactly one trailing newline

## 7. Ownership and persistence

The generator owns only `build/repository-manifest.generated.json`. It returns `GeneratedArtifact`; persistence remains the responsibility of the WP-103 artifact pipeline.

## 8. Registration

`DefaultCliApplicationFactory` registers the generator explicitly. `sif-builder list` exposes `repository.manifest`.

## 9. Testing

Required coverage:

- artifact contract and missing-input diagnostic
- deterministic ordering and metadata fingerprinting
- reference counts and broken reference representation
- stable versioned JSON
- CLI catalog registration

## 10. Acceptance criteria

- Composer autoload resolves all classes.
- PHPUnit passes for the increment and full suite.
- PHPStan level 8 reports no errors.
- CLI lists all four built-in generators.
- Repeated generation with equivalent workspace data produces byte-identical output.
