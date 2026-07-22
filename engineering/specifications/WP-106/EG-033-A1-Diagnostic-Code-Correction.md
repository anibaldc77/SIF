# EG-033-A1 — Metadata Completeness Diagnostic Code Correction

## Status

Approved hotfix.

## Problem

The Builder diagnostic value object accepts diagnostic identifiers composed of a single uppercase family token, one hyphen, and a three-digit number. The functional family originally defined as `META-COMP-201` through `META-COMP-206` contains an additional hyphen and is therefore rejected by `Diagnostic`.

## Decision

Rename the functional diagnostic family without changing semantics:

- `META-COMP-201` → `METACOMP-201`
- `META-COMP-202` → `METACOMP-202`
- `META-COMP-203` → `METACOMP-203`
- `META-COMP-204` → `METACOMP-204`
- `META-COMP-205` → `METACOMP-205`
- `META-COMP-206` → `METACOMP-206`

`ANALYZER-101` remains unchanged.

## Compatibility

No public released version used the invalid identifiers successfully because construction failed before a diagnostic could be returned. The correction therefore restores intended behavior without breaking a valid runtime contract.
