---
id: WP-238-I1-REVIEW
title: WP-238 I1 Architecture Review
summary: Revisa la arquitectura inicial de Identity Governance y Security Administration.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - identity
  - architecture-review
depends_on:
  - EG-473
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-238 I1 Architecture Review

## Alcance revisado

Se incorpora la base de gobierno de accesos:

- subjects;
- entitlements;
- assignments;
- access review decisions;
- review items;
- catalog/provider/publisher contracts.

## Hallazgos

- Governance no reemplaza Authorization.
- SCIM continúa siendo la frontera de provisioning.
- Assignments son temporalmente explícitos.
- Review decisions no ejecutan revocación directamente.
- Storage y proveedores quedan detrás de contracts.

## Decisión

La arquitectura es apta para continuar con catálogo, asignaciones efectivas y campañas de revisión en I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
