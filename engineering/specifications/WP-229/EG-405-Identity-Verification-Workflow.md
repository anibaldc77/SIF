---
id: EG-405
title: Flujo seguro de verificación de identidad
summary: Define emisión anti-enumeración, entrega desacoplada, consumo único y activación verificable para identidad o contacto.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - identity-verification
  - one-time-token
depends_on:
  - EG-401
  - EG-402
  - EG-403
  - EG-404
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-405 — Identity Verification Workflow

## Objetivo

Definir un flujo seguro y neutral para verificar una identidad o un canal de contacto sin reutilizar el propósito de restablecimiento de contraseña.

## Decisiones normativas

- Toda solicitud pública devuelve una respuesta genérica, exista o no la identidad.
- Sólo identidades activas generan un desafío y activan la entrega.
- El propósito `identity_verification` es independiente de `password_reset`.
- Un token emitido para un propósito no puede consumirse en otro.
- La emisión revoca desafíos pendientes anteriores del mismo sujeto y propósito.
- El token en claro sólo atraviesa el límite de entrega; el almacenamiento conserva exclusivamente su digest.
- La confirmación consume el desafío una sola vez antes de ejecutar la activación.
- La activación se delega a `IdentityVerificationActivatorInterface` y no presupone BaseModel, PDO, email o SMS.
- Los rechazos no distinguen token incorrecto, expirado, revocado, consumido, inexistente o de propósito diferente.

## Compatibilidad

La implementación agrega contratos y clases nuevas sin modificar APIs públicas de WP-226, WP-227, WP-228 o entregas anteriores de WP-229.

## Criterios de aceptación

- Respuesta anti-enumeración.
- Emisión sólo para identidad activa.
- Consumo único.
- Rechazo de reutilización cruzada de propósito.
- Activación desacoplada.
- PHPUnit, PHPStan y Builder sin diagnósticos.
