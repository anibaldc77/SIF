# SIF #17 — canonical migration template

Replaces references to nonexistent migration contracts with existing descriptor and PDO SQL operation values. The public generator signature remains compatible. Loading an artifact performs no database access. The read-only placeholder is explicitly documented; rollback defaults to disabled and checksums cover normalized source content.

The product integration test materializes and loads the generated PHP, validates supported types, dispatches SQL through MigrationRuntime and PdoMigrationSqlOperationHandler, records history and detects checksum drift. PDO is mocked, so this is not a live SQL Server test. CRLF/LF checksum equivalence is covered.

Validation: `composer quality` on PHP 8.2.32; see recorded execution evidence in this task. Canonical authoring guidance is in `engineering/migrations/APPLICATION-SKELETON-MIGRATION-AUTHORING.md`.
