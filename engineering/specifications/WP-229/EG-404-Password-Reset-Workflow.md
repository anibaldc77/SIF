---
id: EG-404
title: Flujo seguro de restablecimiento de contraseña
summary: Define solicitud anti-enumeración, emisión, entrega desacoplada, consumo único y reemplazo seguro del hash de contraseña.
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
  - password-reset
  - one-time-token
depends_on:
  - EG-401
  - EG-402
  - EG-403
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-404 — Password Reset Workflow

## Objetivo

Definir un flujo de restablecimiento de contraseña seguro, neutral respecto del canal de entrega y desacoplado de la persistencia concreta.

## Decisiones normativas

- Toda solicitud pública devuelve una aceptación genérica, exista o no la identidad.
- Sólo identidades activas generan un desafío y activan la entrega.
- La emisión revoca desafíos pendientes anteriores del mismo sujeto y propósito.
- El sujeto persistido del desafío es el identificador estable de identidad, no el lookup proporcionado por el usuario.
- El token en claro se entrega mediante `RecoveryChallengeDeliveryInterface` y nunca se almacena.
- El reemplazo de contraseña exige propósito `password_reset`, token válido, desafío pendiente y no vencido.
- El hash de reemplazo se calcula antes de consumir el desafío; una vez consumido, el token no puede reutilizarse.
- Si la escritura final falla, el desafío permanece consumido por seguridad y el usuario debe solicitar uno nuevo.
- Los adaptadores persistentes que requieran atomicidad entre challenge store y password store deben coordinarla transaccionalmente fuera del Core.
- Las respuestas de rechazo no distinguen token incorrecto, expirado, revocado, consumido o inexistente.

## Compatibilidad

El workflow se integra sobre contratos de WP-228 y WP-229 sin modificar APIs consolidadas.

## Criterios de aceptación

- Respuesta anti-enumeración.
- Emisión sólo para cuenta activa.
- Revocación de desafíos anteriores.
- Consumo único.
- Reemplazo del hash sólo después de validación.
- Rechazo sin filtración de causas internas.
- PHPUnit, PHPStan y Builder sin diagnósticos.
