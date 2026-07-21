# EG-017-A1 — BuilderResult Serialization Compatibility

## Decision

`BuilderResult::jsonSerialize()` retains the historical top-level key
`diagnostic_counts` while also exposing the richer `statistics` structure
introduced by EG-017.

## Rationale

`BuilderResult` is a public serializable value object. Removing an existing key
constitutes an avoidable backward-incompatible change. The compatibility key is
derived from `ExecutionStatistics::diagnosticsBySeverity`, so both projections
remain consistent and deterministic.

## Resulting structure

- `diagnostic_counts`: backward-compatible severity map.
- `statistics.diagnostics_by_severity`: canonical reporting statistics map.

No deprecation is introduced in this amendment.
