---
id: WP-231-I1-REVIEW
title: WP-231 I1 Architecture Review
summary: Revisa la separación entre autenticación persistente y confianza de dispositivo y sus límites de almacenamiento.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - trusted-device
  - architecture-review
depends_on:
  - EG-417
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I1 Architecture Review

## Alcance revisado

Se revisa la arquitectura inicial para credenciales persistentes y concesiones de dispositivo confiable.

## Hallazgos

- El selector y el digest del validador quedan diferenciados desde el modelo inicial.
- Los snapshots no exponen material reutilizable.
- La expiración absoluta es una propiedad del dominio y no de la cookie.
- La confianza de dispositivo no autentica ni eleva el nivel de autenticación.
- Los stores son independientes de HTTP, Cookie, Session, MFA y persistencia concreta.

## Riesgo evitado

Un diseño que reutilizara la credencial `remember-me` como prueba de dispositivo confiable permitiría que una credencial robada adquiriera implícitamente privilegios de confianza y pudiera degradar futuras políticas MFA.

La separación adoptada evita ese acoplamiento antes de que forme parte de la API pública.

## Decisión

La arquitectura es apta para continuar hacia generación segura de tokens selector/validator, digest y material de cookie en I2.
