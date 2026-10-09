# SIF #17 — canonical migration template

Replaces references to nonexistent migration contracts with existing descriptor and PDO SQL operation values. The public generator signature remains compatible. Loading an artifact performs no database access. The read-only placeholder is explicitly documented; rollback defaults to disabled and checksums cover normalized source content.

The product integration test materializes and loads the generated PHP, validates supported types, dispatches SQL through MigrationRuntime and PdoMigrationSqlOperationHandler, records history and detects checksum drift. PDO is mocked, so this is not a live SQL Server test. CRLF/LF checksum equivalence is covered.

Validation: `composer quality` on PHP 8.2.32 passes with 1,423 tests, 3,777 assertions and two pre-existing skips; PHPStan level 8 and the configured style check pass. Canonical authoring guidance is in `engineering/migrations/APPLICATION-SKELETON-MIGRATION-AUTHORING.md`.

Consumer validation also exposed two adjacent generated CLI defects: missing required factory arguments and dependency-installed launcher autoload resolution. Both are corrected in Foundation's template/launcher, with a generated installed-layout CLI execution test. Generated HTTP entrypoints now boot providers and narrow the application to HttpAwareApplicationInterface before accessing HTTP, so generated consumer PHP also passes level-8 analysis.
