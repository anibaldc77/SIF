---
id: WP-236-I1-REVIEW
title: WP-236 I1 Architecture Review
summary: Revisa la arquitectura inicial de SAML 2.0, metadata y trust contracts.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-236
tags:
  - security
  - saml
  - federation
  - architecture-review
depends_on:
  - EG-457
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-236 I1 Architecture Review

## Alcance revisado

Se incorpora la base SAML 2.0:

- entity ids;
- endpoints;
- metadata IdP;
- metadata SP;
- fingerprints SHA-256;
- metadata provider contract;
- trust store contract.

## Hallazgos

- Foundation permanece transport-neutral.
- No existe parsing XML en I1.
- Trust queda separado de metadata discovery.
- Los certificados se representan por fingerprints fuertes.
- No existe dependencia concreta de proveedor.
- SP e IdP mantienen metadata diferenciada.

## Riesgo evitado

Acoplar metadata, fetch HTTP, parsing XML y trust en una sola implementación haría difícil reemplazar transportes, cache o proveedores y complicaría las políticas de seguridad.

## Decisión

La arquitectura es apta para continuar con XML metadata parsing y validación estructural en I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
