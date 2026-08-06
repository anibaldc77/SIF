---
id: WP-229-I4-REVIEW
title: Revisión de implementación WP-229 I4
summary: Revisa el flujo seguro y anti-enumeración de restablecimiento de contraseña.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - review
  - security
  - password-reset
depends_on:
  - EG-404
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-229 I4 — Implementation Review

## Alcance revisado

La implementación incorpora solicitud genérica, emisión de desafío para identidad activa, entrega desacoplada, invalidación de anteriores, confirmación por consumo único y reemplazo del hash.

## Evaluación arquitectónica

- El lookup de usuario no se persiste como sujeto del desafío.
- La identidad inexistente o inactiva no altera la respuesta pública.
- El canal de entrega queda detrás de un contrato neutral.
- El token en claro sólo atraviesa el límite de entrega.
- La validación de propósito impide reutilización cruzada.
- El reemplazo del hash depende de contratos de WP-228 y no de BaseModel o PDO.
- Los rechazos se colapsan en un resultado público único.

## Riesgo controlado

La coordinación entre el consumo del desafío y la escritura del nuevo hash no puede ser atómica entre almacenes heterogéneos desde el Core. Se adopta una postura fail-closed: el desafío se consume antes de la escritura final y, ante fallo, debe emitirse uno nuevo. Adaptadores institucionales pueden ofrecer coordinación transaccional.

## Resultado

I4 habilita un password reset completo sin acoplar SIF a correo, SMS, BaseModel, PDO o una infraestructura distribuida concreta.
