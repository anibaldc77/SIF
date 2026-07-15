# Coding Standards

Production PHP targets PHP 8.2, uses `declare(strict_types=1)`, PSR-12, explicit types, and PHPDoc where it conveys contracts not represented by the type system. New code must have a single responsibility and must not introduce global state, helpers, singletons, or unapproved dependencies.

Run `composer style:check` before review. PHP-CS-Fixer is authoritative for formatting; PHPStan level 8 is authoritative for static analysis.
