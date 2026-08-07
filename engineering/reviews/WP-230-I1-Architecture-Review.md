---
id: WP-230-I1-REVIEW
title: WP-230 I1 Architecture Review
summary: Revisa la arquitectura neutral de desafíos, factores y elevación de nivel para autenticación multifactor.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - multi-factor-authentication
  - architecture-review
depends_on:
  - EG-409
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-230 I1 Architecture Review

## Alcance revisado

El incremento define el vocabulario y los límites iniciales de MFA: identificador, identidad objetivo, factor, propósito, nivel requerido, estado, vigencia y contrato de almacenamiento. No implementa TOTP, códigos de recuperación, consumo, replay protection, HTTP, CLI ni persistencia concreta.

## Hallazgos

- `MultiFactorType` es un value object extensible y evita que agregar factores futuros rompa una API pública basada en enum.
- Los propósitos de continuación de autenticación y step-up permanecen separados.
- `AuthenticationLevel` se reutiliza como contrato común, evitando una segunda escala incompatible.
- El snapshot no expone la identidad directa ni material secreto.
- La expiración se normaliza a UTC y tiene límite inclusivo.
- El contrato de almacenamiento permanece neutral respecto de BaseModel, PDO, Redis y proveedores MFA.

## Riesgos revisados

La implementación inicial no debe interpretarse como un ciclo de vida completo. I2–I4 deberán definir material secreto, verificación concreta y transiciones atómicas antes de que un desafío pueda elevar un principal autenticado.

## Compatibilidad

El incremento es aditivo y no modifica los contratos públicos de WP-226, WP-227, WP-228 o WP-229. Los consumidores existentes no requieren migración.

## Decisión

WP-230 I1 es apto para integración cuando PHPUnit, PHPStan y las validaciones gobernadas finalicen sin errores ni diagnósticos.
