# WP-000 — Repository Standards

## Purpose

WP-000 establishes repository hygiene, contributor governance, quality automation, and release-safe metadata for SIF 2.0.0-alpha1. It does not change framework behavior or architecture.

## Scope

The Work Package owns root repository standards, GitHub collaboration assets, engineering handbook material, Composer quality scripts, PHP-CS-Fixer configuration, and the implementation report.

## Acceptance criteria

- Required ignored paths are excluded from Git while lockfiles, documentation, reviews, and GitHub files remain trackable.
- CI validates PHP 8.2, 8.3, and 8.4 using Composer cache and no secrets.
- Dependabot checks Composer and GitHub Actions weekly.
- Quality Gate enforces validation, tests, PHPStan level 8, PHP-CS-Fixer dry-run, metadata, documentation, report, and a clean tree.
- Repository licensing and community-health documents are present.
