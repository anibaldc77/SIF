---
id: WP-248-I1-REVIEW
title: WP-248 I1 Architecture Review
summary: Revisa la arquitectura inicial para adapters SD-JWT VC e ISO mdoc sobre la plataforma de Verifiable Credentials de SIF.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-248
tags:
  - security
  - verifiable-credentials
  - sd-jwt-vc
  - mdoc
  - credential-format
  - architecture-review
depends_on:
  - EG-553
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-248 I1 Architecture Review

## Alcance revisado

Se incorporan formatos de alta garantía explícitos, profile versionado, processing context/assessment y contratos separados para SD-JWT VC, ISO mdoc y policy de formato.

## Hallazgos

- Se reutilizan los boundaries genéricos de WP-244.
- SD-JWT VC e ISO mdoc no contaminan el núcleo con librerías concretas.
- El versionado queda desacoplado del identificador de formato.
- Issuer trust, signature, claims y holder binding permanecen controles separados.
- La arquitectura tolera evolución de especificaciones sin romper contratos públicos.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
