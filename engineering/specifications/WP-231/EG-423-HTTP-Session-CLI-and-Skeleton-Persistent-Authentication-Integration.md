---
id: EG-423
title: Integración HTTP, Session, CLI y Skeleton de autenticación persistente
summary: Define transporte de cookie sensible, restauración de sesión y operaciones CLI opt-in para WP-231 I7.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - http
  - session
  - cli
depends_on:
  - EG-422
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-423 — Integración HTTP, Session, CLI y Skeleton

## Objetivo

Llevar la autenticación persistente al borde HTTP y operativo sin introducir registro global automático ni duplicar responsabilidades de Cookie, Session o Routing.

## Cookie

El payload de cookie:

- transporta selector y validator;
- está redactado en texto y depuración;
- no puede serializarse;
- sólo expone el valor completo mediante callback explícito.

SIF no fija en este incremento nombre, dominio, path, SameSite ni tiempos de cookie. Esas decisiones pertenecen a la aplicación y a la configuración de seguridad HTTP.

## Restauración

La restauración HTTP:

1. parsea el valor de cookie;
2. delega en `PersistentSessionRestorationService`;
3. crea sesión únicamente si la credencial es válida;
4. exige rotación;
5. devuelve material de cookie sustituto.

La aplicación debe reemplazar la cookie anterior después de una restauración exitosa.

## CLI

Se incorporan comandos opt-in para:

- inspección sanitizada;
- revocación individual.

Los comandos no muestran validator ni digest reutilizable.

## Skeleton

El ejemplo de referencia documenta las responsabilidades que permanecen en la aplicación.

## Seguridad

No existe restauración silenciosa de una sesión ya autenticada.

El token anterior debe dejar de utilizarse inmediatamente después de una restauración exitosa.

## Compatibilidad

No se registra routing ni CLI de forma automática.

## Criterios de aceptación

- Payload sensible redactado.
- Restauración devuelve cookie rotada.
- CLI inspecciona sin exponer secretos.
- Revocación impide restauración.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
