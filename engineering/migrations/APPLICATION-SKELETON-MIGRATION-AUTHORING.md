# ApplicationSkeleton migration authoring (SIF #17)

Generated migration files return an array with `descriptor` (`MigrationDescriptor`) and `operation` (`PdoMigrationSqlOperation`). These are the supported Migration/PDO contracts. There is no `MigrationInterface` or `MigrationContext` API.

The generated `SELECT 1` is an explicit read-only placeholder, not a schema migration. Replace it with reviewed SQL before deployment. Supply one `PdoMigrationSqlStatement` per database statement; do not include client-side batch separators such as SQL Server `GO`. Parameters use the existing named-parameter API. Keep the database selection in the connection configuration.

Loading a migration creates values only. Register `descriptor` in `MigrationRegistry` and `operation` in `PdoMigrationSqlOperationCatalog`, then compose with `PdoMigrationAdapterFactory` and the application's explicit connection. Provision the history store intentionally, obtain a runtime plan, review its dry run and execute with a `MigrationExecutionAuthorization` bound to that plan's fingerprint. No authorization or connection is embedded in the generated artifact.

The descriptor checksum covers the complete file with normalized LF endings. Keep SQL inline in that file or deliberately include external SQL content in checksum calculation. Never edit an already applied migration: add a new migration. Changing the file is detected against history; moving it or checking it out with CRLF does not change the checksum.

Rollback is disabled by default. Add down statements only when safe; descriptor reversibility is derived from the operation. Dependencies and version can be supplied through `MigrationDescriptor` when the application requires them.

The integration test loads generated PHP, executes its SQL operation through the real selector/executor/runtime with a mocked PDO boundary and in-memory history/lock/transactions, and checks history integrity and checksum drift. This proves API compatibility; live database compatibility requires separate integration validation against the application's database platform.
