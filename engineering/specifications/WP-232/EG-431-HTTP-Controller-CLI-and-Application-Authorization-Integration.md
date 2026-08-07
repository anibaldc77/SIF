---
id: EG-431
title: Integración HTTP, Controller, CLI y Application de autorización avanzada
summary: Define bridges opt-in para consumir autorización RBAC/ABAC desde controladores, HTTP, CLI y aplicaciones sin acoplar decisiones a respuestas concretas.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - http
  - controller
  - cli
  - application
depends_on:
  - EG-430
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-431 — Integración HTTP, Controller, CLI y Application

## Objetivo

Exponer la autorización avanzada de WP-232 en bordes de aplicación sin registrar comportamiento global ni elegir estrategias HTTP por el consumidor.

## AdvancedAuthorizationRequest

Agrupa:

- principal autenticado;
- atributos resource;
- atributos environment.

No obtiene información de globales.

## Guard

`AdvancedAuthorizationGuard` devuelve el `AuthorizationDecision` canónico.

También ofrece `isAllowed()` como conveniencia.

No crea respuestas HTTP.

## Controller bridge

`ControllerAuthorizationBridge` permite a un controlador solicitar la decisión y aplicar luego su propia estrategia:

- 403;
- 404 para ocultar existencia;
- respuesta API;
- excepción controlada.

WP-232 no impone una de ellas.

## CLI

El comando de inspección utiliza exclusivamente diagnósticos sanitizados.

No expone valores resource/environment.

## Registro

Todo es opt-in.

WP-232 I7 no registra:

- rutas;
- middleware global;
- service providers globales;
- comandos automáticamente.

## Seguridad

La autorización no muta autenticación.

No existe elevación de nivel, restauración de sesión ni bypass MFA desde este borde.

## Criterios de aceptación

- Guard retorna decisión canónica.
- Controller conserva estrategia HTTP.
- CLI usa diagnósticos sanitizados.
- Integración opt-in.
- Sin mutación de autenticación.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
