---
id: WP-233-I3-REVIEW
title: WP-233 I3 Implementation Review
summary: Revisa arquitectura JWT, claims mapping y validación por policy sin dependencia de JWKS.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - jwt
  - implementation-review
depends_on:
  - EG-435
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-233 I3 Implementation Review

## Alcance revisado

Se incorpora el modelo JWT, mapping de claims, policy de validación y verificación de firma detrás de contrato.

## Hallazgos

- JWT es una implementación del contrato de access token, no el contrato mismo.
- Algoritmos se restringen mediante allow-list.
- Issuer, audience y tiempos se validan.
- Claims adicionales se limitan a valores escalares.
- JWKS y transporte HTTP permanecen fuera del incremento.

## Riesgo evitado

Separar `JwtSignatureVerifierInterface` evita que el Core quede unido prematuramente a una librería criptográfica o proveedor de claves específico.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
