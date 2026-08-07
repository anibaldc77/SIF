---
id: WP-236-I2-REVIEW
title: WP-236 I2 Implementation Review
summary: Revisa parsing XML seguro de metadata SAML, validación estructural y extracción de certificados.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-236
tags:
  - security
  - saml
  - metadata
  - xml
  - implementation-review
depends_on:
  - EG-458
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-236 I2 Implementation Review

## Alcance revisado

Se incorpora parser nativo de metadata IdP y extracción de fingerprints de certificados.

## Hallazgos

- DOM y XPath se utilizan con namespaces explícitos.
- Acceso de red XML queda deshabilitado.
- Parsing no implica trust.
- Metadata inválida falla de forma explícita.
- Certificados se normalizan antes de calcular SHA-256.
- No existe transporte ni código específico de proveedor.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
