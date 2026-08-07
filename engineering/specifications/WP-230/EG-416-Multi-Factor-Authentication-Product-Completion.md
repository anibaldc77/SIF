---
id: EG-416
title: Cierre de producto de autenticación multifactor
summary: Define los criterios integrales de cierre, hardening y compatibilidad de WP-230 I8.
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
  - product-completion
depends_on:
  - EG-415
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-416 — Cierre de producto de autenticación multifactor

## Objetivo

Cerrar WP-230 con validación integral de enrolamiento TOTP, desafíos MFA, códigos de recuperación, elevación de sesión y protección contra replay.

## Invariantes finales

- TOTP utiliza secretos encapsulados y adaptadores RFC 6238.
- La activación consume el contador utilizado como prueba de posesión.
- Los contadores aceptados son estrictamente crecientes.
- Los códigos de recuperación se almacenan como digest y se consumen una sola vez.
- Los desafíos validan identidad, propósito, factor, vigencia y estado pendiente.
- La elevación conserva identidad y atributos y actualiza la evidencia.
- La sesión se regenera únicamente después de una satisfacción válida.
- Los payloads, snapshots y comandos no exponen secretos.
- Los stores productivos deben implementar transiciones atómicas.
- Routing, CSRF, autorización administrativa y persistencia concreta permanecen opt-in.

## Compatibilidad

WP-230 extiende WP-226, WP-227, WP-228 y WP-229 sin modificar sus contratos públicos ni forzar MFA a aplicaciones existentes.

## Criterios de aceptación

- Prueba de producto end-to-end para TOTP.
- Prueba de producto end-to-end para códigos de recuperación.
- Rechazo de replay.
- PHPUnit completo sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
