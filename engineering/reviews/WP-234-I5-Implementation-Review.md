---
id: WP-234-I5-REVIEW
title: WP-234 I5 Implementation Review
summary: Revisa account linking, provisioning gobernado y mapping de identidad federada al principal local.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - federation
  - account-linking
  - implementation-review
depends_on:
  - EG-445
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-234 I5 Implementation Review

## Alcance revisado

Se incorpora resolución de vínculos federados, provisioning opt-in y mapping al `AuthenticatedPrincipal` existente.

## Hallazgos

- `issuer + subject` continúa siendo la identidad federada estable.
- No se usa email como clave automática.
- El provisioning automático requiere política explícita.
- El principal utiliza la identidad local.
- El contexto federado queda disponible como atributos.
- La capa no crea sesión ni escribe persistencia directamente.

## Riesgo evitado

Vincular cuentas por email de forma implícita puede producir account takeover cuando el atributo cambia, se recicla o no tiene garantías suficientes del proveedor.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
