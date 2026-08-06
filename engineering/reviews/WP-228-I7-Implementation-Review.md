---
id: WP-228-I7-REVIEW
title: Revisión de implementación WP-228 I7
summary: Revisa la integración HTTP de autenticación por contraseña, establecimiento de sesión, cierre de sesión y wiring de Application Skeleton.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - review
  - security
  - password
  - http
  - session
  - skeleton
depends_on:
  - EG-399
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I7 — Implementation Review

## Resultado

La implementación integra autenticación por contraseña con HTTP y sesión mediante adaptadores opt-in, sin ampliar los contratos centrales ni introducir persistencia concreta.

## Decisiones

- La transformación JSON se concentra en `PasswordLoginRequest`.
- El establecimiento y cierre de sesión se concentran en `PasswordSessionLoginService`.
- Los endpoints no revelan la causa concreta del rechazo.
- La regeneración se delega al ciclo de sesión existente.
- El Application Skeleton conserva la decisión sobre rutas, CSRF, cookies y presentación.

## Compatibilidad

Todos los nuevos constructores y tipos son aditivos. No se modifican APIs públicas de WP-226, WP-227 ni las implementaciones I1–I6 de WP-228.
