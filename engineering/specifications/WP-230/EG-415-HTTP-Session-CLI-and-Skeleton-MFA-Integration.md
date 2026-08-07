---
id: EG-415
title: Integración HTTP, Session, CLI y Skeleton de MFA
summary: Define payloads sensibles, elevación de sesión, endpoints opt-in y comandos administrativos para WP-230 I7.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - mfa
  - http
  - session
  - cli
depends_on:
  - EG-414
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-415 — Integración HTTP, Session, CLI y Skeleton de MFA

## Objetivo

Integrar la satisfacción TOTP y por código de recuperación con HTTP, sesión autenticada y CLI, sin registrar rutas ni comandos globales por defecto.

## Invariantes

- Los payloads de códigos se redactan y no pueden serializarse.
- Sólo un principal autenticado puede elevar su sesión.
- La sesión y el `SecurityContext` se actualizan únicamente tras satisfacción válida.
- La elevación solicita regeneración del identificador de sesión.
- Las respuestas HTTP usan `Cache-Control: no-store` y `Pragma: no-cache`.
- Los comandos de inspección no exponen identidad directa ni secretos.
- La revocación CLI es una operación administrativa opt-in.
- El Core no decide routing, CSRF ni autorización administrativa.

## Compatibilidad

La integración utiliza contratos públicos de WP-226, WP-227 y los servicios MFA de WP-230 sin cambiar sus firmas.

## Criterios de aceptación

- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
