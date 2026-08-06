---
id: EG-399
title: Integración HTTP de login, logout y sesión para autenticación por contraseña
summary: Define la integración HTTP segura para autenticación por contraseña, establecimiento de sesión y cierre de sesión.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - specification
  - security
  - password
  - http
  - session
  - skeleton
depends_on:
  - EG-398
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-399 — HTTP Password Login, Logout and Session Integration

## Propósito

Definir un adaptador HTTP opt-in que traduzca solicitudes JSON de inicio de sesión a los contratos de autenticación de WP-227 y WP-228, y que establezca o elimine el principal mediante el ciclo de sesión de WP-226.

## Invariantes

- Las contraseñas nunca se serializan, registran ni incorporan a respuestas.
- Los rechazos de autenticación producen una respuesta pública genérica.
- El éxito solicita regeneración de identificador de sesión.
- El cierre de sesión elimina el snapshot del principal y vuelve a solicitar regeneración.
- El adaptador HTTP no conoce BaseModel, PDO, tablas de usuario ni proveedores externos.
- Las respuestas de autenticación usan `Cache-Control: no-store` y `Pragma: no-cache`.

## Alcance

Incluye request model JSON, servicio de login con sesión, endpoint de login y endpoint de logout. El registro de rutas y la selección de protección CSRF permanecen bajo responsabilidad del Application Skeleton.
